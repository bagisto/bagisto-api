<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\Checkout\Repositories\CartRepository;

/**
 * Provides input data for CartToken mutations and queries without attempting to load a resource by ID.
 * API Platform's default behavior tries to load a resource by ID, but CartToken doesn't have
 * a database table - it's a DTO-based API. This provider simply passes through the input.
 */
class CartTokenMutationProvider implements ProviderInterface
{
    public function __construct(
        protected CartRepository $cartRepository,
    ) {}

    /**
     * Provide cart input or data for mutations and queries.
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): CartInput|CartData|array|null {
        $operationName = $operation->getName();

        // Handle collection operation (get all carts for authenticated user)
        if ($operationName === 'get_collection') {
            $request = $context['request'] ?? null;
            $token = null;

            if ($request) {
                $authHeader = $request->headers->get('Authorization', '');
                if (preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
                    $token = $matches[1];
                }
            }

            if ($token) {
                // Get customer from token and fetch all their carts
                $customer = $this->getCustomerFromToken($token);
                if ($customer) {
                    $carts = $this->cartRepository->findWhere(['customer_id' => $customer->id, 'is_active' => 1])->get();

                    return $carts->map(fn ($cart) => CartData::fromModel($cart))->toArray();
                }
            }

            return [];
        }

        // Handle single item operation (get single cart)
        if ($operationName === 'get') {
            $id = $uriVariables['id'] ?? null;
            if ($id) {
                $cart = $this->cartRepository->find($id);
                if ($cart) {
                    return CartData::fromModel($cart);
                }
            }

            return null;
        }

        if ($operationName === 'readCart') {
            $args = $context['args'] ?? [];
            $cartId = $args['cartId'] ?? null;
            $token = $args['token'] ?? null;

            if ($cartId) {
                $cart = $this->cartRepository->findById($cartId);
                if ($cart) {
                    return CartData::fromModel($cart);
                }
            }

            if ($token) {
                $cart = $this->cartRepository->findWhere(['cart_token' => $token])->first();
                if ($cart) {
                    return CartData::fromModel($cart);
                }
            }

            return new CartData;
        }

        return null;
    }

    /**
     * Get customer from bearer token
     */
    private function getCustomerFromToken(?string $token): ?\Webkul\Customer\Models\Customer
    {
        if (! $token) {
            return null;
        }

        try {
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($personalAccessToken && $personalAccessToken->tokenable) {
                return $personalAccessToken->tokenable;
            }
        } catch (\Exception $e) {
            // Token is invalid
        }

        return null;
    }
}
