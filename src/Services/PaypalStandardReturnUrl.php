<?php

namespace Webkul\BagistoApi\Services;

/**
 * Builds and checks the PayPal Standard return URLs the API hands to PayPal. The cart id is
 * signed into the path rather than the query, because PayPal appends its own query parameters.
 */
class PaypalStandardReturnUrl
{
    /**
     * The URL PayPal sends the customer to after paying.
     */
    public static function success(int $cartId): string
    {
        return route('bagistoapi.paypal-standard.success', ['cartId' => $cartId, 'signature' => self::sign($cartId)]);
    }

    /**
     * The URL PayPal sends the customer to after cancelling.
     */
    public static function cancel(int $cartId): string
    {
        return route('bagistoapi.paypal-standard.cancel', ['cartId' => $cartId, 'signature' => self::sign($cartId)]);
    }

    /**
     * Whether the signature was issued for the cart.
     */
    public static function isValid(int $cartId, string $signature): bool
    {
        return hash_equals(self::sign($cartId), $signature);
    }

    /**
     * Sign the cart id with the application key.
     */
    protected static function sign(int $cartId): string
    {
        return hash_hmac('sha256', 'paypal-standard:'.$cartId, (string) config('app.key'));
    }
}
