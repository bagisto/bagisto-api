<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\OperationFailedException;
use Webkul\CartRule\Exceptions\CouponUsageLimitExceededException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Payment\Facades\Payment;
use Webkul\Sales\Transformers\OrderResource;

/**
 * The checks a cart must pass before an order is placed or a payment is started.
 */
trait ValidatesCheckoutCart
{
    /**
     * Build order data from cart.
     */
    protected function buildOrderDataFromCart($cart): array
    {
        $orderResource = new OrderResource($cart);

        return $orderResource->jsonSerialize();
    }

    /**
     * Create the order from the cart. When a coupon has run out of uses, core drops it, recollects
     * the totals and asks the customer to retry, so the next attempt can succeed.
     */
    protected function createOrderFromCart($cart): mixed
    {
        try {
            return $this->orderRepository->create($this->buildOrderDataFromCart($cart));
        } catch (CouponUsageLimitExceededException) {
            Cart::removeCouponCode();

            Cart::collectTotals();

            throw new InvalidInputException(trans('shop::app.checkout.coupon.usage-limit-exceeded'), 422);
        }
    }

    /**
     * Validate order can be created.
     */
    protected function validateOrderCreation($cart, ?CheckoutAddressInput $input = null): void
    {
        if (! $cart || $cart->items()->count() === 0) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.cart-empty'));
        }

        $customer = $cart->customer;

        if ($customer?->is_suspended) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.account-suspended'));
        }

        if ($customer && ! $customer->status) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.account-inactive'));
        }

        $this->assertGuestCheckoutAllowed($cart);

        $minimumOrderAmount = core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0;
        if (! Cart::haveMinimumOrderAmount()) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.minimum-order-not-met', ['amount' => core()->currency($minimumOrderAmount)]));
        }

        $hasBillingAddress = $input?->billingAddress || $cart->billing_address()->exists();
        if (! $hasBillingAddress) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.billing-address-required'));
        }

        $hasShippingAddress = $input?->shippingAddress || $input?->useForShipping || $cart->shipping_address()->exists();
        if (! $hasShippingAddress && $cart->haveStockableItems()) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.shipping-address-required'));
        }

        $hasEmail = $cart->customer_email || $input?->billingEmail || ($cart->billing_address && $cart->billing_address->email);
        if (! $hasEmail) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.email-required'));
        }

        if ($cart->haveStockableItems()) {
            $hasShippingMethod = $input?->shippingMethod || $cart->shipping_method;
            if (! $hasShippingMethod) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.shipping-method-required'));
            }

            if (! $cart->selected_shipping_rate) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.invalid-shipping-method'));
            }
        }

        $hasPaymentMethod = $input?->paymentMethod || $cart->payment()->exists();
        if (! $hasPaymentMethod) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.payment-method-required'));
        }

        if (! $this->isPaymentMethodAvailable($cart->payment?->method)) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.invalid-payment-method'));
        }
    }

    /**
     * Stop when the cart carries an error, such as an item no longer in stock.
     */
    protected function assertCartHasNoErrors(): void
    {
        if (Cart::hasError()) {
            throw new OperationFailedException(Cart::getErrors()['message'] ?? __('bagistoapi::app.graphql.checkout.cart-empty'));
        }
    }

    /**
     * Apply the storefront's guest checkout rules to a cart that has no customer.
     */
    protected function assertGuestCheckoutAllowed($cart): void
    {
        if ($cart->customer_id) {
            return;
        }

        if (
            ! core()->getConfigData('sales.checkout.shopping_cart.allow_guest_checkout')
            || $cart->hasDownloadableItems()
            || ! $cart->hasGuestCheckoutItems()
        ) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.guest-checkout-not-allowed'));
        }
    }

    /**
     * Whether the method is offered for the current cart.
     */
    protected function isPaymentMethodAvailable(?string $method): bool
    {
        if (! $method) {
            return false;
        }

        return collect(Payment::getSupportedPaymentMethods()['payment_methods'] ?? [])
            ->contains('method', $method);
    }
}
