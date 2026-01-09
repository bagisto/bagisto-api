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

        // Extract token from Authorization header (Bearer token) via TokenHeaderFacade
        $token = TokenHeaderFacade::getAuthorizationBearerToken($request);

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.address.authentication-required'));
        }

        // Handle API Platform's automatic id to addressId mapping
        if (! $data->addressId && isset($uriVariables['id'])) {
            $data->addressId = $uriVariables['id'];
        }

        $customer = $this->getCustomerFromToken($token);
        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.address.invalid-token'));
        }

        return match ($operationName) {
            'create'       => $this->handleAddUpdate($customer, $data),
            'createDelete' => $this->handleDelete($customer, $data),
            'read'         => $this->handleGetAddress($customer, $data),
            'collection'   => $this->handleGetAddresses($customer, $data),
            default        => throw new InvalidInputException(__('bagistoapi::app.graphql.address.unknown-operation')),
        };
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

    private function handleDelete(Customer $customer, CustomerAddressInput $data): array
    {
        if (! $data->addressId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.address.address-id-required'));
        }

        $address = AddressModel::find($data->addressId);
        if (! $address || $address->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.address.address-not-found'));
        }

        Event::dispatch('customer.addresses.delete.before', $data->addressId);
        $address->delete();
        Event::dispatch('customer.addresses.delete.after', $data->addressId);

        return ['message' => __('bagistoapi::app.graphql.address.deleted-successfully'), 'success' => true];
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
