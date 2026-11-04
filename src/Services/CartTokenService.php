<?php

namespace Webkul\BagistoApi\Services;

use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\Checkout\Contracts\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Repositories\CustomerRepository;

class CartTokenService
{
    public function __construct(
        protected CartRepository $cartRepository,
        protected GuestCartTokensRepository $guestCartTokensRepository,
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Get cart by token (guest or customer).
     */
    public function getCartByToken(string $token): ?object
    {
        try {

            $cart = $this->guestCartTokensRepository->findCartByToken($token);

            if ($cart && $cart->is_active) {
                return $cart;
            }

            $customer = $this->customerRepository->findOneByField('token', $token);
            if ($customer) {
                $this->actAsCustomer($customer);

                return $this->cartRepository->findOneWhere([
                    'customer_id' => $customer->id,
                    'is_active' => 1,
                ]);
            }

            $personalAccessToken = PersonalAccessToken::findToken($token);
            if ($personalAccessToken && $personalAccessToken->tokenable instanceof Customer) {
                $customer = $personalAccessToken->tokenable;

                if ($customer) {
                    $this->actAsCustomer($customer);

                    return $this->cartRepository->findOneWhere([
                        'customer_id' => $customer->id,
                        'is_active' => 1,
                    ]);
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * The bearer's cart, active or not: a gateway callback deactivates the cart once it places
     * the order, so a payment confirmation must still find it. A guest token owns exactly one
     * cart; a customer owns every cart carrying their id, the active one when none is named.
     */
    public function getOwnedCart(string $token, ?int $cartId = null): ?object
    {
        try {
            $guestCart = $this->guestCartTokensRepository->findCartByToken($token);

            if ($guestCart) {
                return $cartId === null || (int) $guestCart->id === $cartId ? $guestCart : null;
            }

            $customer = $this->customerRepository->findOneByField('token', $token);

            if (! $customer) {
                $personalAccessToken = PersonalAccessToken::findToken($token);

                $customer = $personalAccessToken?->tokenable instanceof Customer ? $personalAccessToken->tokenable : null;
            }

            if (! $customer) {
                return null;
            }

            $this->actAsCustomer($customer);

            return $this->cartRepository->findOneWhere(array_filter([
                'customer_id' => $customer->id,
                'id' => $cartId,
                'is_active' => $cartId === null ? 1 : null,
            ], fn ($value) => $value !== null));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Make the bearer's customer the current storefront customer for this request, so core
     * resolves their customer group for prices and cart rules instead of the guest group.
     */
    public function actAsCustomer(Customer $customer): void
    {
        Auth::guard('customer')->setUser($customer);
    }

    /**
     * Create an active cart with the same defaults core's Cart::createCart() writes. The
     * customer is passed explicitly, as an API guest must never inherit a session user.
     */
    public function createCart(?Customer $customer = null): Cart
    {
        $data = [
            'is_guest' => 1,
            'is_active' => 1,
            'channel_id' => core()->getCurrentChannel()->id,
            'global_currency_code' => $baseCurrencyCode = core()->getBaseCurrencyCode(),
            'base_currency_code' => $baseCurrencyCode,
            'channel_currency_code' => core()->getChannelBaseCurrencyCode(),
            'cart_currency_code' => core()->getCurrentCurrencyCode(),
        ];

        if ($customer) {
            $data = array_merge($data, [
                'is_guest' => 0,
                'customer_id' => $customer->id,
                'customer_first_name' => $customer->first_name,
                'customer_last_name' => $customer->last_name,
                'customer_email' => $customer->email,
            ]);
        }

        return $this->cartRepository->create($data);
    }

    /**
     * Get cart by direct ID lookup.
     */
    public function getCartById(int $cartId): ?object
    {
        return $this->cartRepository->find($cartId);
    }

    /**
     * Resolve customer from authentication token.
     */
    public function getCustomerByToken(string $token): ?object
    {
        return $this->customerRepository->findOneByField('token', $token);
    }

    /**
     * Get guest token record details.
     */
    public function getGuestTokenRecord(string $token): ?object
    {
        return $this->guestCartTokensRepository->findByToken($token);
    }

    /**
     * Determine token type: 'guest', 'customer', or 'unknown'.
     */
    public function getTokenType(string $token): string
    {
        if ($this->guestCartTokensRepository->findByToken($token)) {
            return 'guest';
        }

        if ($this->customerRepository->findByToken($token)) {
            return 'customer';
        }

        return 'unknown';
    }

    /**
     * Check if token exists and has an associated cart.
     */
    public function isValidToken(string $token): bool
    {
        return $this->getCartByToken($token) !== null;
    }
}
