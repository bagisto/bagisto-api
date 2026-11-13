<?php

namespace Webkul\BagistoApi\Services;

class PaypalStandardReturnUrl
{
    /**
     * The URL PayPal sends the customer to after paying, with the cart signed into the path because PayPal appends its own query.
     */
    public static function success(int $cartId): string
    {
        return route('bagistoapi.paypal_standard.success', ['cartId' => $cartId, 'signature' => self::sign($cartId)]);
    }

    /**
     * The URL PayPal sends the customer to after cancelling.
     */
    public static function cancel(int $cartId): string
    {
        return route('bagistoapi.paypal_standard.cancel', ['cartId' => $cartId, 'signature' => self::sign($cartId)]);
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
