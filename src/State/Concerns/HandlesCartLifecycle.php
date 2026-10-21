<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\OperationFailedException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\Checkout\Facades\Cart as CartFacade;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Customer\Models\Customer;
use Webkul\Shipping\Facades\Shipping;

trait HandlesCartLifecycle
{
    /**
     * Return the given cart with recollected rates and totals, refusing a cart owned by another customer.
     */
    private function handleGetCart(?CartModel $cart, ?Customer $customer, CartInput $data)
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (
            $customer
            && $cart->customer_id !== $customer->id
        ) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.cart.unauthorized-access'));
        }

        if (! $cart->items()->exists()) {
            return (array) CartData::fromModel($cart->load('items.product'));
        }

        CartFacade::setCart($cart);

        if (
            $cart->shipping_method
            && $cart->shipping_address
        ) {
            Shipping::collectRates();
        }

        CartFacade::collectTotals();

        $cart = CartFacade::getCart();

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        $cart->load('items.product');

        $cartData = CartData::fromModel($cart);

        return (array) $cartData;
    }

    /**
     * Return all carts of the authenticated customer.
     */
    private function handleGetCarts(?Customer $customer, CartInput $data): array
    {
        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authenticated-only'));
        }

        $carts = $this->cartRepository->findWhere([
            'customer_id' => $customer->id,
        ])->load('items.product');

        return [
            'carts' => CartData::collection($carts),
        ];
    }

    /**
     * Merge the guest cart into the authenticated customer's cart, adopting it when the customer has none.
     */
    private function handleMergeGuest(?CartModel $guestCart, ?Customer $customer, CartInput $data): array
    {
        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.merge-requires-auth'));
        }

        if (! $guestCart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.guest-cart-not-found'));
        }

        $customerCart = $this->cartRepository->findOneWhere([
            'customer_id' => $customer->id,
            'is_active' => 1,
        ]);

        if ($customerCart) {
            $this->mergeIntoCustomerCart($customerCart, $guestCart);
        } else {
            $customerCart = $this->adoptGuestCart($guestCart, $customer);
        }

        $customerCart = CartModel::find($customerCart->id);

        $cartData = CartData::fromModel($customerCart);
        $cartData->success = true;
        $cartData->message = __('bagistoapi::app.graphql.cart.guest-cart-merged');

        return (array) $cartData;
    }

    /**
     * Turn a guest cart into the customer's cart when the customer has none.
     */
    private function adoptGuestCart(CartModel $guestCart, Customer $customer): CartModel
    {
        $this->cartRepository->update([
            'customer_id' => $customer->id,
            'is_guest' => 0,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'customer_email' => $customer->email,
        ], $guestCart->id);

        $this->guestCartTokensRepository->deleteWhere(['cart_id' => $guestCart->id]);

        $customerCart = CartModel::find($guestCart->id);

        CartFacade::setCart($customerCart);

        CartFacade::collectTotals();

        return $customerCart;
    }

    /**
     * Add the guest cart's items to the customer's existing cart and drop the guest cart.
     */
    private function mergeIntoCustomerCart(CartModel $customerCart, CartModel $guestCart): void
    {
        CartFacade::setCart($customerCart);

        foreach ($guestCart->items as $guestCartItem) {
            try {
                CartFacade::addProduct($guestCartItem->product, $guestCartItem->additional ?? []);
            } catch (\Exception) {
                continue;
            }
        }

        CartFacade::collectTotals();

        CartFacade::removeCart($guestCart);
    }

    /**
     * Resolve the customer from a bearer token and act as them for the cart.
     */
    private function getCustomerFromToken(string $token): ?Customer
    {
        try {
            $personalAccessToken = PersonalAccessToken::findToken($token);

            if (
                $personalAccessToken
                && $personalAccessToken->tokenable instanceof Customer
            ) {
                CartTokenFacade::actAsCustomer($personalAccessToken->tokenable);

                return $personalAccessToken->tokenable;
            }
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }

        return null;
    }

    /**
     * Return the customer's active cart or create one, issuing a session token for a new guest cart.
     */
    private function handleCreateOrGetCart(?Customer $customer, CartInput $data): array
    {
        if ($customer) {
            $cart = $this->cartRepository->findOneWhere([
                'customer_id' => $customer->id,
                'is_active' => 1,
            ]);

            if (! $cart) {
                $cart = CartTokenFacade::createCart($customer);
            }

            $cartData = CartData::fromModel($cart);
            $cartData->isGuest = false;
            $cartData->success = true;
            $cartData->message = __('bagistoapi::app.graphql.cart.using-authenticated-cart');

            return (array) $cartData;
        } else {
            $sessionToken = $this->generateSecureToken();

            $cart = CartTokenFacade::createCart();

            $guestCartData = [
                'cart_id' => $cart->id,
                'token' => $sessionToken,
            ];

            $this->guestCartTokensRepository->create($guestCartData);

            $cartData = CartData::fromModel($cart);
            $cartData->sessionToken = $sessionToken;
            $cartData->cartToken = $sessionToken;
            $cartData->isGuest = true;
            $cartData->success = true;
            $cartData->message = __('bagistoapi::app.graphql.cart.new-guest-cart-created');

            return (array) $cartData;
        }
    }

    /**
     * Generate a UUID token for a guest cart session.
     */
    private function generateSecureToken(): string
    {
        return (string) Str::uuid();
    }
}
