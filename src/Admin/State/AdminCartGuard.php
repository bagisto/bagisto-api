<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Models\Cart;

/**
 * Shared auth + ownership check for the /api/admin/carts/* endpoints.
 *
 * Validates that:
 *   1) a valid admin token is on the request
 *   2) the cart exists
 *   3) the cart is a draft cart (`is_active = 0`) — admin-built carts only.
 *      Active storefront carts (is_active = 1) are the customer's own session
 *      cart and must not be mutated through the admin API.
 */
class AdminCartGuard
{
    /**
     * Resolve and validate the cart for the current request.
     *
     * @throws AuthenticationException if no admin is authenticated
     * @throws ResourceNotFoundException if the cart does not exist
     * @throws AuthorizationException if the cart is an active storefront cart
     */
    public static function resolve(int|string|null $cartId): Cart
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if ($cartId === null || $cartId === '' || (int) basename((string) $cartId) <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cart.not-found'));
        }

        $cart = Cart::find((int) basename((string) $cartId));

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cart.not-found'));
        }

        if ((int) $cart->is_active === 1) {
            throw new AuthorizationException(__('bagistoapi::app.admin.cart.not-draft'));
        }

        return $cart;
    }

    /**
     * Extract the cart id from REST uriVariables, GraphQL args, or request fallback.
     */
    public static function resolveId(array $uriVariables, array $context): int|string|null
    {
        return $uriVariables['id']
            ?? $uriVariables['cartId']
            ?? $context['args']['cartId']
            ?? $context['args']['input']['cartId']
            ?? $context['args']['id']
            ?? $context['args']['input']['id']
            ?? request()->route('id')
            ?? request()->route('cartId')
            ?? request()->input('cartId')
            ?? request()->input('id')
            ?? null;
    }
}
