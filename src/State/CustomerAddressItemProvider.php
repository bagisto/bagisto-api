<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\CustomerAddress;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\Customer;

class CustomerAddressItemProvider implements ProviderInterface
{
    /**
     * Provide.
     */
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

        if (
            ! $address
            || (int) $address->customer_id !== $customerId
        ) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.address.address-not-found'));
        }

        return $address;
    }

    /**
     * The id of the customer behind a customer access token.
     */
    private function getCustomerIdFromToken(string $token): ?int
    {
        return CurrentCustomer::fromToken($token)?->id;
    }
}
