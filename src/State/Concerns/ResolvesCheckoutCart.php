<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\Checkout\Facades\Cart;

/**
 * Resolves the cart of the Bearer token and makes it the current cart, with fresh totals.
 */
trait ResolvesCheckoutCart
{
    protected function resolveCheckoutCart(): object
    {
        $token = TokenHeaderFacade::getAuthorizationBearerToken(request());

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        Cart::setCart($cart);

        Cart::collectTotals();

        return Cart::getCart();
    }
}
