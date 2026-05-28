<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Repositories\CustomerRepository;

class AdminCustomerMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }
        $this->assertPermission($admin);

        $indices = $this->resolveIndices($data, $context);
        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.mass-delete-indices-required'), 422);
        }

        $deleted = [];
        $skipped = [];

        foreach ($indices as $idx) {
            $id = (int) $idx;
            $customer = Customer::find($id);
            if (! $customer) {
                continue;
            }

            if ($this->customerRepository->haveActiveOrders($customer)) {
                $skipped[] = ['id' => $id, 'reason' => __('bagistoapi::app.admin.customer.has-active-orders')];

                continue;
            }

            try {
                Event::dispatch('customer.delete.before', $customer);
                $this->customerRepository->delete($id);
                Event::dispatch('customer.delete.after', $customer);
                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                $skipped[] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        $result = new AdminCustomerMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->skipped = $skipped;
        $result->message = __('bagistoapi::app.admin.customer.mass-delete-success');

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
        if (! in_array('customers.customers.delete', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }
    }

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminCustomerMassDeleteInput && ! empty($data->indices)) {
            return $data->indices;
        }

        $fromArgs = $context['args']['input']['indices'] ?? $context['args']['indices'] ?? null;
        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        $body = request()->input('indices');

        return is_array($body) ? $body : [];
    }
}
