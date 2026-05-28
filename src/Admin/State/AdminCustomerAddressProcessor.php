<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerAddressCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerAddressUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerAddress;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Customer\Repositories\CustomerAddressRepository;

class AdminCustomerAddressProcessor implements ProcessorInterface
{
    public function __construct(
        protected CustomerAddressRepository $addressRepository,
        protected AdminCustomerAddressItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;
        $customerId = (int) ($uriVariables['customerId']
            ?? request()->route('customerId')
            ?? ($context['args']['input']['customerId'] ?? null)
            ?? ($context['args']['input']['customer_id'] ?? null)
            ?? 0);

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminCustomerAddressUpdateInput) {
            $this->assertPermission($admin, 'customers.addresses.delete');
            $id = (int) basename((string) ($data->id ?? ''));

            return $this->handleDelete($customerId, $id);
        }

        if ($data instanceof AdminCustomerAddressCreateInput
            || ($data instanceof AdminCustomerAddress && $operation instanceof Post)) {
            $this->assertPermission($admin, 'customers.addresses.create');
            $input = $this->resolveInput($data, $context, $isGraphQL);
            if ($customerId <= 0) {
                $customerId = (int) ($input['customer_id'] ?? 0);
            }
            $this->assertCustomerExists($customerId);

            return $this->handleCreate($customerId, $input);
        }

        if ($data instanceof AdminCustomerAddressUpdateInput
            || ($data instanceof AdminCustomerAddress && $operation instanceof Put)) {
            $this->assertPermission($admin, 'customers.addresses.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) ($data->id ?? '')));

            return $this->handleUpdate($customerId, $id, $this->resolveInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'customers.addresses.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($customerId, $id);
        }

        return null;
    }

    protected function handleCreate(int $customerId, array $input): AdminCustomerAddress
    {
        $rules = [
            'first_name' => ['required', 'string'],
            'last_name'  => ['required', 'string'],
            'address'    => ['required', 'string'],
            'city'       => ['required', 'string'],
            'country'    => ['required', 'string'],
            'postcode'   => ['required', 'string'],
            'phone'      => ['required', 'string'],
        ];
        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        Event::dispatch('customer.addresses.create.before');

        if (! empty($input['default_address'])) {
            CustomerAddress::where('customer_id', $customerId)
                ->where('default_address', 1)
                ->update(['default_address' => 0]);
        }

        $payload = [
            'customer_id'     => $customerId,
            'first_name'      => $input['first_name'],
            'last_name'       => $input['last_name'],
            'company_name'    => $input['company_name'] ?? null,
            'vat_id'          => $input['vat_id'] ?? null,
            'address'         => is_array($input['address'] ?? null) ? implode(PHP_EOL, array_filter($input['address'])) : ($input['address'] ?? ''),
            'city'            => $input['city'],
            'state'           => $input['state'] ?? null,
            'country'         => $input['country'],
            'postcode'        => $input['postcode'],
            'phone'           => $input['phone'],
            'email'           => $input['email'] ?? null,
            'default_address' => (int) (! empty($input['default_address'])),
            'address_type'    => CustomerAddress::ADDRESS_TYPE,
        ];

        $address = $this->addressRepository->create($payload);

        Event::dispatch('customer.addresses.create.after', $address);

        return $this->itemProvider->toDto($address->fresh());
    }

    protected function handleUpdate(int $customerId, int $id, array $input): AdminCustomerAddress
    {
        $address = CustomerAddress::find($id);
        if (! $address) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.address.not-found'));
        }
        if ($customerId > 0 && (int) $address->customer_id !== $customerId) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.address.not-owned'), 403);
        }

        Event::dispatch('customer.addresses.update.before', $id);

        if (! empty($input['default_address'])) {
            CustomerAddress::where('customer_id', $address->customer_id)
                ->where('default_address', 1)
                ->update(['default_address' => 0]);
        }

        $patch = [];
        foreach (['first_name', 'last_name', 'company_name', 'vat_id', 'city', 'state', 'country', 'postcode', 'phone', 'email'] as $f) {
            if (array_key_exists($f, $input) && $input[$f] !== null) {
                $patch[$f] = $input[$f];
            }
        }
        if (array_key_exists('address', $input) && $input['address'] !== null) {
            $patch['address'] = is_array($input['address']) ? implode(PHP_EOL, array_filter($input['address'])) : $input['address'];
        }
        if (array_key_exists('default_address', $input) && $input['default_address'] !== null) {
            $patch['default_address'] = (int) (bool) $input['default_address'];
        }

        $this->addressRepository->update($patch, $id);

        Event::dispatch('customer.addresses.update.after', $address->fresh());

        return $this->itemProvider->toDto($address->fresh());
    }

    protected function handleDelete(int $customerId, int $id): array
    {
        $address = CustomerAddress::find($id);
        if (! $address) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.address.not-found'));
        }
        if ($customerId > 0 && (int) $address->customer_id !== $customerId) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.address.not-owned'), 403);
        }

        Event::dispatch('customer.addresses.delete.before', $id);

        $this->addressRepository->delete($id);

        Event::dispatch('customer.addresses.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.customer.address.deleted')];
    }

    protected function assertCustomerExists(int $customerId): void
    {
        if ($customerId <= 0 || ! Customer::find($customerId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }
    }

    protected function resolveInput(mixed $data, array $context, bool $isGraphQL): array
    {
        if ($isGraphQL) {
            $raw = $context['args']['input'] ?? $context['args'] ?? [];
            unset($raw['id'], $raw['clientMutationId']);

            return $this->normalizeKeys($raw);
        }

        return request()->all();
    }

    protected function normalizeKeys(array $args): array
    {
        $map = [
            'firstName'      => 'first_name',
            'lastName'       => 'last_name',
            'companyName'    => 'company_name',
            'vatId'          => 'vat_id',
            'defaultAddress' => 'default_address',
            'customerId'     => 'customer_id',
        ];
        $out = [];
        foreach ($args as $k => $v) {
            $out[$map[$k] ?? $k] = $v;
        }

        return $out;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }
        if (($role->permission_type ?? null) === 'all') {
            return;
        }
        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }
        if (! in_array($permission, $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }
    }
}
