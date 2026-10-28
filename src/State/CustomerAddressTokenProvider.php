<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\CustomerAddressTokenOutput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;

class CustomerAddressTokenProvider implements ProviderInterface
{
    /**
     * Provide the requested address when it belongs to the authenticated customer.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.address.authentication-required'));
        }

        $customerId = $this->getCustomerIdFromToken($token);

        if ($customerId === null) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.address.invalid-token'));
        }

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? ''));

        $address = $id ? CustomerAddress::find($id) : null;

        if (
            ! $address
            || (int) $address->customer_id !== $customerId
        ) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.address.address-not-found'));
        }

        return $this->toOutput($address);
    }

    /**
     * Map an address model to the token address output.
     */
    protected function toOutput(CustomerAddress $address): CustomerAddressTokenOutput
    {
        $addressLines = array_values(array_filter(explode("\n", $address->address ?? '')));

        $output = new CustomerAddressTokenOutput;
        $output->id = $address->id;
        $output->address_id = $address->id;
        $output->first_name = $address->first_name;
        $output->last_name = $address->last_name;
        $output->company_name = $address->company_name;
        $output->vat_id = $address->vat_id;
        $output->email = $address->email;
        $output->phone = $address->phone;
        $output->address1 = $addressLines[0] ?? null;
        $output->address2 = $addressLines[1] ?? null;
        $output->country = $address->country;
        $output->state = $address->state;
        $output->city = $address->city;
        $output->postcode = $address->postcode;
        $output->default_address = (bool) $address->default_address;

        return $output;
    }

    /**
     * The id of the customer behind a customer access token, or null when it is not one.
     */
    protected function getCustomerIdFromToken(string $token): ?int
    {
        return CurrentCustomer::fromToken($token)?->id;
    }
}
