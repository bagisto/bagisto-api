<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerMassUpdateStatus;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Customer\Repositories\CustomerRepository;

class AdminCustomerMassUpdateStatusProcessor implements ProcessorInterface
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }
        $this->assertPermission($admin);

        $payload = $this->resolvePayload($data, $context);
        $indices = $payload['indices'] ?? [];
        $value = isset($payload['value']) ? (int) $payload['value'] : null;

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.mass-update-indices-required'), 422);
        }
        if ($value === null || ! in_array($value, [0, 1], true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.mass-update-value-invalid'), 422);
        }

        $updated = [];
        foreach ($indices as $idx) {
            $id = (int) $idx;
            try {
                Event::dispatch('customer.update.before', $id);
                $customer = $this->customerRepository->update(['status' => $value], $id);
                Event::dispatch('customer.update.after', $customer);
                $updated[] = $id;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $result = new AdminCustomerMassUpdateStatus;
        $result->id = 1;
        $result->updated = $updated;
        $result->value = $value;
        $result->message = __('bagistoapi::app.admin.customer.mass-update-success');

        return $result;
    }

    protected function assertPermission(object $admin): void
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
        if (! in_array('customers.customers.edit', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }
    }

    protected function resolvePayload(mixed $data, array $context): array
    {
        if ($data instanceof AdminCustomerMassUpdateStatusInput) {
            return ['indices' => $data->indices ?? [], 'value' => $data->value];
        }

        $args = $context['args']['input'] ?? $context['args'] ?? null;
        if (is_array($args)) {
            return ['indices' => $args['indices'] ?? [], 'value' => $args['value'] ?? null];
        }

        return [
            'indices' => is_array(request()->input('indices')) ? request()->input('indices') : [],
            'value'   => request()->input('value'),
        ];
    }
}
