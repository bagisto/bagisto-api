<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\CustomerAddressInput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress as AddressModel;

class CustomerAddressTokenProcessor implements ProcessorInterface
{
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): mixed {
        $request = Request::instance() ?? ($context['request'] ?? null);
        $operationName = $operation->getName();

        // For REST requests, the API Platform Eloquent deserialization may not properly
        // populate camelCase DTO properties. Read directly from the request body as fallback.
        if ($data instanceof CustomerAddressInput && $request && in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $requestData = $request->json()->all();
            if (! empty($requestData)) {
                foreach ($requestData as $key => $value) {
                    $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
                    if (property_exists($data, $camelKey) && $data->$camelKey === null) {
                        $data->$camelKey = $value;
                    }
                }
            }
        }

        // Extract token from Authorization header (Bearer token) via TokenHeaderFacade
        $token = TokenHeaderFacade::getAuthorizationBearerToken($request);

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.address.authentication-required'));
        }

        // URL {id} parameter takes priority over body addressId for REST operations
        if (isset($uriVariables['id'])) {
            $data->addressId = (int) $uriVariables['id'];
        }

        $customer = $this->getCustomerFromToken($token);
        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.address.invalid-token'));
        }

        /** Determine if this is a delete operation */
        $shortName = $operation->getShortName() ?? '';
        $isDeleteOperation = $shortName === 'DeleteCustomerAddress'
            || $operationName === 'createDelete'
            || $operation instanceof \ApiPlatform\Metadata\Delete;

        if ($isDeleteOperation) {
            return $this->handleDelete($customer, $data);
        }

        // Handle both GraphQL operation names and REST operation types
        if ($operationName === 'create' || $operation instanceof \ApiPlatform\Metadata\Post || $operation instanceof \ApiPlatform\Metadata\Put) {
            return $this->handleAddUpdate($customer, $data);
        }

        if ($operationName === 'read') {
            return $this->handleGetAddress($customer, $data);
        }

        if ($operationName === 'collection') {
            return $this->handleGetAddresses($customer, $data);
        }

        throw new InvalidInputException(__('bagistoapi::app.graphql.address.unknown-operation'));
    }

    private function handleAddUpdate(Customer $customer, CustomerAddressInput $data): array
    {
        if ($data->addressId) {
            return $this->handleUpdate($customer, $data);
        }

        return $this->handleAdd($customer, $data);
    }

    private function handleAdd(Customer $customer, CustomerAddressInput $data): array
    {
        Event::dispatch('customer.addresses.create.before');

        $address = new AddressModel;
        $address->customer_id = $customer->id;
        $address->address_type = 'customer';

        $this->mapInputToAddress($address, $data);

        if ($data->defaultAddress) {
            AddressModel::where('customer_id', $customer->id)
                ->where('address_type', 'customer')
                ->update(['default_address' => false]);
            $address->default_address = true;
        }

        $address->save();

        Event::dispatch('customer.addresses.create.after', $address);

        return $this->mapAddressToResponse($address);
    }

    private function handleUpdate(Customer $customer, CustomerAddressInput $data): array
    {
        if (! $data->addressId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.address.address-id-required'));
        }

        $address = AddressModel::find($data->addressId);
        if (! $address || $address->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.address.address-not-found'));
        }

        Event::dispatch('customer.addresses.update.before', $data->addressId);

        $this->mapInputToAddress($address, $data, true);

        if ($data->defaultAddress) {
            AddressModel::where('customer_id', $customer->id)
                ->where('address_type', 'customer')
                ->where('id', '!=', $address->id)
                ->update(['default_address' => false]);
            $address->default_address = true;
        }

        $address->save();

        Event::dispatch('customer.addresses.update.after', $address);

        return $this->mapAddressToResponse($address);
    }

    private function handleDelete(Customer $customer, mixed $data): CustomerAddressInput
    {
        // Get address ID from DTO (GraphQL) or Eloquent model (REST DELETE)
        $addressId = $data->addressId ?? $data->id ?? null;

        if (! $addressId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.address.address-id-required'));
        }

        $address = AddressModel::find($addressId);
        if (! $address || $address->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.address.address-not-found'));
        }

        /** Capture address data before deletion for response */
        $response = $this->mapAddressToInput($address);

        Event::dispatch('customer.addresses.delete.before', $addressId);
        $address->delete();
        Event::dispatch('customer.addresses.delete.after', $addressId);

        return $response;
    }

    private function handleGetAddress(Customer $customer, CustomerAddressInput $data): array
    {
        if (! $data->addressId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.address.address-id-required'));
        }

        $address = AddressModel::find($data->addressId);
        if (! $address || $address->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.address.address-not-found'));
        }

        return $this->mapAddressToResponse($address);
    }

    private function handleGetAddresses(Customer $customer, CustomerAddressInput $data): array
    {
        $addresses = $customer->addresses()->get();
        $addressArray = [];
        foreach ($addresses as $address) {
            $addressArray[] = $this->mapAddressToResponse($address);
        }

        return ['addresses' => $addressArray, 'success' => true];
    }

    private function mapInputToAddress(AddressModel $address, CustomerAddressInput $data, bool $isUpdate = false): void
    {
        if ($isUpdate) {
            if ($data->firstName !== null) {
                $address->first_name = $data->firstName;
            }
            if ($data->lastName !== null) {
                $address->last_name = $data->lastName;
            }
            if ($data->email !== null) {
                $address->email = $data->email;
            }
            if ($data->phone !== null) {
                $address->phone = $data->phone;
            }
            if ($data->address1 !== null || $data->address2 !== null) {
                $addr = array_filter([$data->address1 ?? '', $data->address2 ?? '']);
                $address->address = implode(PHP_EOL, $addr);
            }
            if ($data->country !== null) {
                $address->country = $data->country;
            }
            if ($data->state !== null) {
                $address->state = $data->state;
            }
            if ($data->city !== null) {
                $address->city = $data->city;
            }
            if ($data->postcode !== null) {
                $address->postcode = $data->postcode;
            }
            if ($data->useForShipping !== null) {
                $address->use_for_shipping = $data->useForShipping;
            }
            if ($data->defaultAddress !== null) {
                $address->default_address = $data->defaultAddress;
            }
        } else {
            $address->first_name = $data->firstName ?? null;
            $address->last_name = $data->lastName ?? null;
            $address->email = $data->email ?? null;
            $address->phone = $data->phone ?? null;
            $addr = array_filter([$data->address1 ?? '', $data->address2 ?? '']);
            $address->address = implode(PHP_EOL, $addr);
            $address->country = $data->country ?? null;
            $address->state = $data->state ?? null;
            $address->city = $data->city ?? null;
            $address->postcode = $data->postcode ?? null;
            $address->use_for_shipping = $data->useForShipping ?? false;
            $address->default_address = $data->defaultAddress ?? false;
        }
    }

    private function mapAddressToResponse(AddressModel $address): array
    {
        $addressLines = array_filter(explode("\n", $address->address ?? ''));

        return [
            'id'             => $address->id,
            'addressId'      => $address->id,
            'firstName'      => $address->first_name,
            'lastName'       => $address->last_name,
            'email'          => $address->email,
            'phone'          => $address->phone,
            'address1'       => $addressLines[0] ?? null,
            'address2'       => $addressLines[1] ?? null,
            'country'        => $address->country,
            'state'          => $address->state,
            'city'           => $address->city,
            'postcode'       => $address->postcode,
            'useForShipping' => (bool) $address->use_for_shipping,
            'defaultAddress' => (bool) $address->default_address,
        ];
    }

    /**
     * Map an address model to a CustomerAddressInput DTO for delete response
     */
    private function mapAddressToInput(AddressModel $address): CustomerAddressInput
    {
        $addressLines = array_filter(explode("\n", $address->address ?? ''));

        $input = new CustomerAddressInput;
        $input->id = $address->id;
        $input->addressId = $address->id;
        $input->firstName = $address->first_name;
        $input->lastName = $address->last_name;
        $input->email = $address->email;
        $input->phone = $address->phone;
        $input->address1 = $addressLines[0] ?? null;
        $input->address2 = $addressLines[1] ?? null;
        $input->country = $address->country;
        $input->state = $address->state;
        $input->city = $address->city;
        $input->postcode = $address->postcode;
        $input->useForShipping = (bool) $address->use_for_shipping;
        $input->defaultAddress = (bool) $address->default_address;

        return $input;
    }

    private function getCustomerFromToken(string $token): ?Customer
    {
        try {
            if (strpos($token, '|') === false) {
                return null;
            }

            [$id, $hash] = explode('|', $token, 2);

            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if (! $personalAccessToken) {
                return null;
            }

            if (! $personalAccessToken->tokenable instanceof Customer) {
                return null;
            }

            return $personalAccessToken->tokenable;
        } catch (\Exception $e) {
            return null;
        }
    }
}
