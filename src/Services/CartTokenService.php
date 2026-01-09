<?php

namespace Webkul\BagistoApi\Services;

use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
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
                return $this->cartRepository->findOneWhere([
                    'customer_id' => $customer->id,
                    'is_active'   => 1,
                ]);
            }

            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($personalAccessToken && $personalAccessToken->tokenable instanceof Customer) {
                $customer = $personalAccessToken->tokenable;

                if ($customer) {
                    return $this->cartRepository->findOneWhere([
                        'customer_id' => $customer->id,
                        'is_active'   => 1,
                    ]);
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
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
