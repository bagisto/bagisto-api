<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\Checkout\Facades\Cart;

trait ResolvesCheckoutCart
{
    /**
     * The bearer's active cart, set as core's current cart with its totals collected; core drops a cart left empty.
     */
    protected function resolveCheckoutCart(): object
    {
        $cart = CartTokenFacade::getCartByToken($this->bearerToken());

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        Cart::setCart($cart);

        Cart::collectTotals();

        $cart = Cart::getCart();

        if (! $cart) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.cart-empty'), 422);
        }

        return $cart;
    }

    /**
     * The bearer's cart named by a payment reference, active or already turned into an order.
     */
    protected function resolveReferencedCart(string $reference): object
    {
        $cart = ctype_digit($reference)
            ? CartTokenFacade::getOwnedCart($this->bearerToken(), (int) $reference)
            : null;

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.checkout.payment-reference-not-found'));
        }

        return $cart;
    }

    /**
     * The request's Bearer token, which names a guest cart or a signed-in customer.
     */
    protected function bearerToken(): string
    {
        $token = TokenHeaderFacade::getAuthorizationBearerToken(request());

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        return $token;
    }
}
