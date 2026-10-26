<?php

namespace Webkul\BagistoApi\Support;

use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Auth\CustomerApiGuard;
use Webkul\BagistoApi\Exception\CustomerAuthException;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\Customer\Models\Customer;

class CurrentCustomer
{
    /**
     * The name of the guard storefront customer access tokens authenticate through.
     */
    public const GUARD = 'customer-api';

    /**
     * The customer behind the request's access token, or null for a guest.
     */
    public static function user(): ?Customer
    {
        $customer = static::guard()->user();

        return $customer instanceof Customer ? $customer : null;
    }

    /**
     * Whether the request carries a valid customer access token.
     */
    public static function check(): bool
    {
        return static::user() !== null;
    }

    /**
     * The session behind the request's access token.
     */
    public static function session(): ?CustomerApiSession
    {
        return static::guard()->session();
    }

    /**
     * Why the request's access token was rejected, or null when it was accepted or absent.
     */
    public static function failure(): ?CustomerAuthException
    {
        return static::guard()->failure();
    }

    /**
     * The session behind the request's access token, refusing the request with the reason when there is none.
     *
     * @throws CustomerAuthException
     */
    public static function requireSession(): CustomerApiSession
    {
        $session = static::session();

        if ($session) {
            return $session;
        }

        throw static::failure() ?? new CustomerAuthException(
            trans('bagistoapi::app.graphql.auth.token-required'),
            CustomerAuthException::CODE_UNAUTHENTICATED,
        );
    }

    /**
     * The customer behind a token passed in the input rather than the Authorization header.
     */
    public static function fromToken(?string $token): ?Customer
    {
        if (
            $token === null
            || $token === ''
        ) {
            return null;
        }

        $customer = static::guard()->customerForToken($token);

        return $customer instanceof Customer ? $customer : null;
    }

    /**
     * The customer API guard.
     */
    protected static function guard(): CustomerApiGuard
    {
        return Auth::guard(static::GUARD);
    }
}
