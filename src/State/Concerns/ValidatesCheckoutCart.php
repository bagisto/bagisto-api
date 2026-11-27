<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Payment\Facades\Payment;
use Webkul\Sales\Transformers\OrderResource;

trait ValidatesCheckoutCart
{
    /**
     * Build order data from cart.
     */
    protected function buildOrderDataFromCart($cart): array
    {
        return (new OrderResource($cart))->jsonSerialize();
    }

    /**
     * Validate that the cart can become an order, reading what the request sends before what the cart holds.
     */
    protected function validateOrderCreation($cart, ?CheckoutAddressInput $input = null): void
    {
        if (
            ! $cart
            || $cart->items()->count() === 0
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.cart-empty'), 422);
        }

        $customer = $cart->customer;

        if ($customer?->is_suspended) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.checkout.account-suspended'));
        }

        if (
            $customer
            && ! $customer->status
        ) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.checkout.account-inactive'));
        }

        $this->assertGuestCheckoutAllowed($cart);

        $minimumOrderAmount = core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0;

        if (! Cart::haveMinimumOrderAmount()) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.minimum-order-not-met', ['amount' => core()->currency($minimumOrderAmount)]), 422);
        }

        $hasBillingAddress = $input?->billingAddress || $cart->billing_address()->exists();

        if (! $hasBillingAddress) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.billing-address-required'), 422);
        }

        $hasShippingAddress = $input?->shippingAddress || $input?->useForShipping || $cart->shipping_address()->exists();

        if (
            ! $hasShippingAddress
            && $cart->haveStockableItems()
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.shipping-address-required'), 422);
        }

        $hasEmail = $cart->customer_email || $input?->billingEmail || $cart->billing_address?->email;

        if (! $hasEmail) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.email-required'), 422);
        }

        if ($cart->haveStockableItems()) {
            $hasShippingMethod = $input?->shippingMethod || $cart->shipping_method;

            if (! $hasShippingMethod) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.shipping-method-required'), 422);
            }

            if (! $cart->selected_shipping_rate) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.invalid-shipping-method'), 422);
            }
        }

        $hasPaymentMethod = $input?->paymentMethod || $cart->payment()->exists();

        if (! $hasPaymentMethod) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-method-required'), 422);
        }

        if (! $this->isPaymentMethodAvailable($cart->payment?->method)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.invalid-payment-method'), 422);
        }
    }

    /**
     * Stop when the cart carries an error, such as an item no longer in stock.
     */
    protected function assertCartHasNoErrors(): void
    {
        if (Cart::hasError()) {
            throw new InvalidInputException(Cart::getErrors()['message'] ?? __('bagistoapi::app.graphql.checkout.cart-empty'), 422);
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
            throw new AuthorizationException(__('bagistoapi::app.graphql.checkout.guest-checkout-not-allowed'));
        }
    }

    /**
     * Whether the payment method is offered for the current cart.
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
