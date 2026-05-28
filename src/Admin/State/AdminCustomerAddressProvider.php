<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerAddress;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\Customer;

/**
 * Returns all saved addresses for a customer — used by the Create-Order
 * screen's billing / shipping picker. Read-only, not paginated by the UI but
 * wrapped in the standard admin `{ data, meta }` envelope for consistency.
 */
class AdminCustomerAddressProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $customerId = (int) (
            $uriVariables['customerId']
            ?? $context['args']['customerId']
            ?? $context['args']['customer_id']
            ?? request()->route('customerId')
            ?? 0
        );

        if ($customerId <= 0 && ! empty($context['args']) && is_array($context['args'])) {
            foreach ($context['args'] as $v) {
                if (is_array($v) && ! empty($v['customerId'])) {
                    $customerId = (int) $v['customerId'];
                    break;
                }
            }
        }

        if ($customerId <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $customer = Customer::find($customerId);

        if (! $customer) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $rows = $customer->addresses
            ->map(fn ($address) => $this->toDto($address))
            ->all();

        $total = count($rows);
        $perPage = max($total, 1);

        return new Paginator(new LengthAwarePaginator($rows, $total, $perPage, 1));
    }

    protected function toDto($address): AdminCustomerAddress
    {
        $dto = new AdminCustomerAddress;

        $dto->id = $address->id;
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
