<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\OperationFailedException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Facades\Cart as CartFacade;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Customer\Models\Customer;
use Webkul\Shipping\Facades\Shipping;

trait HandlesCartCouponsAndExtras
{
    /**
     * Handle applying coupon code
     */
    private function handleApplyCoupon(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (! $data->couponCode) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.coupon-code-required'));
        }

        if ($customer && $cart->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.cart.unauthorized-access'));
        }

        CartFacade::setCart($cart);

        try {
            CartFacade::setCouponCode($data->couponCode)->collectTotals();
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }

        $cart = CartFacade::getCart();

        $cartData = CartData::fromModel($cart);
        // Bagisto's setCouponCode/collectTotals does NOT throw for unknown codes — it
        // silently writes the column without applying a discount. Treat the coupon as
        // applied only when there is a matching cart_rule_coupons row.
        $applied = ! empty($cart->coupon_code)
            && $cart->coupon_code === $data->couponCode
            && \DB::table('cart_rule_coupons')->where('code', $data->couponCode)->exists();

        if (! $applied) {
            // Strip the unverified code so subsequent reads don't lie about a discount.
            $cart->coupon_code = null;
            $cart->save();
            $cartData->couponCode = null;
        }

        $cartData->success = $applied;
        $cartData->message = $applied
            ? __('bagistoapi::app.graphql.cart.coupon-applied')
            : __('bagistoapi::app.graphql.cart.apply-coupon-failed');

        return (array) $cartData;
    }

    /**
     * Handle removing coupon code from cart
     */
    private function handleRemoveCoupon(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        CartFacade::setCart($cart);

        try {
            CartFacade::removeCouponCode()->collectTotals();

            $cart = CartFacade::getCart();

            if (! $cart) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.cart.remove-coupon-failed'));
            }

            $cartData = CartData::fromModel($cart);
            $cartData->success = true;
            $cartData->message = __('bagistoapi::app.graphql.cart.coupon-removed');

            return (array) $cartData;
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Handle moving items to wishlist
     */
    private function handleMoveToWishlist(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (! $data->itemIds || ! is_array($data->itemIds) || empty($data->itemIds)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.item-ids-required'));
        }

        CartFacade::setCart($cart);

        foreach ($data->itemIds as $index => $itemId) {
            try {
                $qty = $data->quantities[$index] ?? 1;
                CartFacade::moveToWishlist($itemId, $qty);
            } catch (\Exception $exception) {
                throw new OperationFailedException($exception->getMessage(), 0, $exception);
            }
        }

        CartFacade::collectTotals();

        $cartId = $cart->id;
        $cart = CartModel::find($cartId);

        if (! $cart) {
            $cartData = new CartData;
            $cartData->id = $cartId;
            $cartData->itemsCount = 0;
            $cartData->subtotal = 0;
            $cartData->grandTotal = 0;
            $cartData->taxAmount = 0;
            $cartData->discountAmount = 0;
            $cartData->shippingAmount = 0;
            $cartData->items = [];

            return (array) $cartData;
        }

        return (array) CartData::fromModel($cart);
    }

    /**
     * Handle estimating shipping methods and tax
     */
    private function handleEstimateShipping(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (! $data->country || ! $data->state || ! $data->postcode) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.address-data-required'));
        }

        CartFacade::setCart($cart);

        try {
            $address = (new CartAddress)->fill([
                'country' => $data->country,
                'state' => $data->state,
                'postcode' => $data->postcode,
                'cart_id' => $cart->id,
            ]);

            $cart->setRelation('billing_address', $address);
            $cart->setRelation('shipping_address', $address);

            CartFacade::setCart($cart);

            if ($data->shippingMethod) {
                CartFacade::saveShippingMethod($data->shippingMethod);
            }

            CartFacade::collectTotals();

            return (array) CartData::fromModel(CartFacade::getCart());
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }
    }
}
