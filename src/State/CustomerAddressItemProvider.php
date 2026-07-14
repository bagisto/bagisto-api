<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\CustomerAddress;
use Webkul\Customer\Models\Customer;

/**
 * Serves a single customer address, scoped to the authenticated customer.
 */
class CustomerAddressItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.token-required'));
        }

        $customerId = $this->getCustomerIdFromToken($token);

        if ($customerId === null) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.customer-addresses.invalid-or-expired-token'));
        }

        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            return null;
        }

        $address = CustomerAddress::find($id);

        if (! $address || (int) $address->customer_id !== $customerId) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.address.address-not-found'));
        }

        return $address;
    }

    private function getCustomerIdFromToken(string $token): ?int
    {
        try {
            if (! str_contains($token, '|')) {
                return null;
            }

            $personalAccessToken = PersonalAccessToken::findToken($token);

            if (! $personalAccessToken || ! $personalAccessToken->tokenable instanceof Customer) {
                return null;
            }

            return (int) $personalAccessToken->tokenable->id;
        } catch (\Throwable) {
            return null;
        }
    }
}
