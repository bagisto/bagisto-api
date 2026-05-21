<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Checkout\Models\Cart;

/**
 * Centralised "Create-Order" sequence enforcement for the admin draft-cart
 * flow. Every Wave 3 endpoint that depends on a prior step calls these
 * guards explicitly so the API surfaces a precise HTTP 409 (Conflict) message
 * instead of relying on the monolith's implicit ordering. Conflicts are 409
 * because they describe a state mismatch — the resource exists, the request
 * is valid, but the cart isn't in the right state yet.
 */
class AdminCartSequenceGuard
{
    /**
     * Cart must have at least one item.
     */
    public static function requireItems(Cart $cart): void
    {
        $cart->refresh();
        $count = (int) ($cart->items_count ?? $cart->items()->count());

        if ($count <= 0) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.place-order.empty-cart'), 409);
        }
    }

    /**
     * Cart must have BOTH billing and shipping addresses saved.
     * (Bagisto stores billing as a 1:1 row + shipping address as another row
     * when use_for_shipping is true the shipping row mirrors billing.)
     */
    public static function requireAddresses(Cart $cart, string $messageKey = 'bagistoapi::app.admin.cart.addresses-required-for-shipping'): void
    {
        $cart->refresh();
        $cart->load(['billing_address', 'shipping_address']);

        $hasShippable = (bool) $cart->haveStockableItems();

        if (! $cart->billing_address) {
            throw new InvalidInputException(__($messageKey), 409);
        }

        if ($hasShippable && ! $cart->shipping_address) {
            throw new InvalidInputException(__($messageKey), 409);
        }
    }

    /**
     * Cart must have a shipping method set (only when there are stockable items).
     */
    public static function requireShippingMethod(Cart $cart, string $messageKey = 'bagistoapi::app.admin.cart.shipping-required-for-payment'): void
    {
        $cart->refresh();

        if (! $cart->haveStockableItems()) {
            return; // Virtual / downloadable only — no shipping required.
        }

        if (empty($cart->shipping_method) || ! $cart->selected_shipping_rate) {
            throw new InvalidInputException(__($messageKey), 409);
        }
    }

    /**
     * Cart must have a payment method set.
     */
    public static function requirePaymentMethod(Cart $cart, string $messageKey = 'bagistoapi::app.admin.cart.place-order.payment-required'): void
    {
        $cart->refresh();
        $cart->load(['payment']);

        if (! $cart->payment || empty($cart->payment->method)) {
            throw new InvalidInputException(__($messageKey), 409);
        }
    }
}
