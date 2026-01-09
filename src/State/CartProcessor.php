<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\OperationFailedException;
use Webkul\BagistoApi\Models\Cart as CartModel;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Product\Repositories\ProductRepository;

class CartProcessor implements ProcessorInterface
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected CartRuleCouponRepository $cartRuleCouponRepository
    ) {}

    /**
     * Process cart operations.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CartModel
    {
        $operationName = $operation->getName();

        return match ($operationName) {
            'addToCart'               => $this->handleAddToCart($data),
            'updateCartItem'          => $this->handleUpdateCartItem($data),
            'removeFromCart'          => $this->handleRemoveFromCart($data),
            'destroySelected'         => $this->handleDestroySelected($data),
            'moveToWishlist'          => $this->handleMoveToWishlist($data),
            'emptyCart'               => $this->handleEmptyCart($data),
            'estimateShippingMethods' => $this->handleEstimateShippingMethods($data),
            'applyCoupon'             => $this->handleApplyCoupon($data),
            'removeCoupon'            => $this->handleRemoveCoupon($data),
            default                   => $data,
        };
    }

    /**
     * Handle add to cart (from CartController->store).
     */
    private function handleAddToCart($data): CartModel
    {
        $this->validateAddToCart($data);

        $product = $this->productRepository->with('parent')->findOrFail($data->product_id);

        if (! $product->status) {
            throw new InvalidInputException(trans('shop::app.checkout.cart.inactive-add'));
        }

        try {
            $cart = Cart::addProduct($product, (array) $data);

            if ($data->is_buy_now ?? false) {
                Cart::deActivateCart();
            }

            Cart::collectTotals();

            return $this->buildCartResponse($cart);
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage());
        }
    }

    /**
     * Handle update cart items (from CartController->update).
     */
    private function handleUpdateCartItem($data): CartModel
    {
        try {
            Cart::updateItems((array) $data);
            Cart::collectTotals();

            return $this->buildCartResponse(Cart::getCart());
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage());
        }
    }

    /**
     * Handle remove from cart (from CartController->destroy).
     */
    private function handleRemoveFromCart($data): CartModel
    {
        $this->validateRemoveFromCart($data);

        Cart::removeItem($data->cart_item_id);
        Cart::collectTotals();

        return $this->buildCartResponse(Cart::getCart());
    }

    /**
     * Handle remove selected items (from CartController->destroySelected).
     */
    private function handleDestroySelected($data): CartModel
    {
        $ids = $data->ids ?? $data->getIds();

        if (empty($ids) || ! is_array($ids)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.select-items-to-remove'));
        }

        foreach ($ids as $id) {
            Cart::removeItem($id);
        }

        Cart::collectTotals();

        return $this->buildCartResponse(Cart::getCart());
    }

    /**
     * Handle move to wishlist (from CartController->moveToWishlist).
     */
    private function handleMoveToWishlist($data): CartModel
    {
        $ids = $data->ids ?? $data->getIds();
        $qty = $data->qty ?? $data->getQty() ?? [];

        if (empty($ids) || ! is_array($ids)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.select-items-to-move-wishlist'));
        }

        foreach ($ids as $index => $id) {
            $quantity = $qty[$index] ?? 1;
            Cart::moveToWishlist($id, $quantity);
        }

        Cart::collectTotals();

        return $this->buildCartResponse(Cart::getCart());
    }

    /**
     * Handle empty cart.
     */
    private function handleEmptyCart($data): CartModel
    {
        $cart = Cart::getCart();

        if ($cart) {
            foreach ($cart->items as $item) {
                Cart::removeItem($item->id);
            }
        }

        Cart::collectTotals();

        return $this->buildCartResponse(Cart::getCart());
    }

    /**
     * Handle estimate shipping methods (from CartController->estimateShippingMethods).
     */
    private function handleEstimateShippingMethods($data): CartModel
    {
        $this->validateEstimateShipping($data);

        $cart = Cart::getCart();

        $address = (new CartAddress)->fill([
            'country'  => $data->country,
            'state'    => $data->state,
            'postcode' => $data->postcode,
            'cart_id'  => $cart->id,
        ]);

        $cart->setRelation('billing_address', $address);
        $cart->setRelation('shipping_address', $address);

        Cart::setCart($cart);

        if (isset($data->shipping_method) && ! empty($data->shipping_method)) {
            Cart::saveShippingMethod($data->shipping_method);
        }

        Cart::collectTotals();

        return $this->buildCartResponse(Cart::getCart());
    }

    /**
     * Handle apply coupon (from CartController->storeCoupon).
     */
    private function handleApplyCoupon($data): CartModel
    {
        if (empty($data->code)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.coupon-code-required'));
        }

        $coupon = $this->cartRuleCouponRepository->findOneByField('code', $data->code);

        if (! $coupon) {
            throw new InvalidInputException(trans('shop::app.checkout.coupon.invalid'));
        }

        if (! $coupon->cart_rule->status) {
            throw new InvalidInputException(trans('shop::app.checkout.coupon.error'));
        }

        $cart = Cart::getCart();

        if ($cart->coupon_code == $coupon->code) {
            throw new InvalidInputException(trans('shop::app.checkout.coupon.already-applied'));
        }

        Cart::setCouponCode($coupon->code)->collectTotals();

        if (Cart::getCart()->coupon_code != $coupon->code) {
            throw new InvalidInputException(trans('shop::app.checkout.coupon.error'));
        }

        return $this->buildCartResponse(Cart::getCart());
    }

    /**
     * Handle remove coupon (from CartController->destroyCoupon).
     */
    private function handleRemoveCoupon($data): CartModel
    {
        Cart::removeCouponCode()->collectTotals();

        return $this->buildCartResponse(Cart::getCart());
    }

    /**
     * Validate add to cart input.
     */
    private function validateAddToCart($data): void
    {
        $rules = [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'sometimes|integer|min:1',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make((array) $data, $rules);

        if ($validator->fails()) {
            $messages = implode(', ', $validator->errors()->all());
            throw new InvalidInputException($messages);
        }
    }

    /**
     * Validate remove from cart input.
     */
    private function validateRemoveFromCart($data): void
    {
        $rules = [
            'cart_item_id' => 'required|integer|exists:cart_items,id',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make((array) $data, $rules);

        if ($validator->fails()) {
            $messages = implode(', ', $validator->errors()->all());
            throw new InvalidInputException($messages);
        }
    }

    /**
     * Validate estimate shipping input.
     */
    private function validateEstimateShipping($data): void
    {
        $rules = [
            'country'  => 'required',
            'state'    => 'required',
            'postcode' => 'required',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make((array) $data, $rules);

        if ($validator->fails()) {
            $messages = implode(', ', $validator->errors()->all());
            throw new InvalidInputException($messages);
        }
    }

    /**
     * Build cart response object.
     */
    private function buildCartResponse($cart): CartModel
    {
        if (! $cart) {
            return new CartModel;
        }

        $cartResponse = new CartModel;
        $cartResponse->id = (string) $cart->id;
        $cartResponse->customer_id = $cart->customer_id;
        $cartResponse->quote_id = $cart->quote_id;

        $items = [];
        foreach ($cart->items as $item) {
            $items[] = [
                'id'           => $item->id,
                'product_id'   => $item->product_id,
                'quantity'     => $item->quantity,
                'sku'          => $item->product?->sku,
                'name'         => $item->product?->name,
                'price'        => $item->price,
                'base_price'   => $item->base_price,
                'total'        => $item->total,
                'base_total'   => $item->base_total,
                'options'      => $item->options ? json_decode($item->options, true) : [],
            ];
        }

        $cartResponse->items = $items;
        $cartResponse->subtotal = (float) ($cart->sub_total ?? 0);
        $cartResponse->base_subtotal = (float) ($cart->base_sub_total ?? 0);
        $cartResponse->tax = (float) ($cart->tax_total ?? 0);
        $cartResponse->base_tax = (float) ($cart->base_tax_total ?? 0);
        $cartResponse->discount = (float) ($cart->discount_amount ?? 0);
        $cartResponse->base_discount = (float) ($cart->base_discount_amount ?? 0);
        $cartResponse->total = (float) ($cart->grand_total ?? 0);
        $cartResponse->base_total = (float) ($cart->base_grand_total ?? 0);
        $cartResponse->items_count = (int) ($cart->items_count ?? 0);
        $cartResponse->coupon_code = $cart->coupon_code;
        $cartResponse->created_at = $cart->created_at?->toIso8601String();
        $cartResponse->updated_at = $cart->updated_at?->toIso8601String();

        return $cartResponse;
    }
}
