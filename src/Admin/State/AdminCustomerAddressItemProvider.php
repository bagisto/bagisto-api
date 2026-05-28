<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerAddress;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\CustomerAddress;

class AdminCustomerAddressItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminCustomerAddress
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $customerId = (int) ($uriVariables['customerId'] ?? $context['args']['customerId'] ?? request()->route('customerId') ?? 0);
        $id = (int) ($uriVariables['id'] ?? basename((string) ($context['args']['id'] ?? '')) ?? 0);

        if ($customerId <= 0 || $id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.address.not-found'));
        }

        $address = CustomerAddress::where('customer_id', $customerId)->find($id);
        if (! $address) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.address.not-found'));
        }

        return $this->toDto($address);
    }

    public function toDto($address): AdminCustomerAddress
    {
        $dto = new AdminCustomerAddress;
        $dto->id = $address->id;
        $dto->customerId = $address->customer_id;
        $dto->addressType = $address->address_type;
        $dto->firstName = $address->first_name;
        $dto->lastName = $address->last_name;
        $dto->companyName = $address->company_name;
        $dto->address = $address->address;
        $dto->city = $address->city;
        $dto->state = $address->state;
        $dto->country = $address->country;
        $dto->postcode = $address->postcode;
        $dto->email = $address->email;
        $dto->phone = $address->phone;
        $dto->vatId = $address->vat_id;
        $dto->defaultAddress = (bool) $address->default_address;

        return $dto;
    }
}
