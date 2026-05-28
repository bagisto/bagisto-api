<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGroupMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGroupMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Customer\Repositories\CustomerGroupRepository;

class AdminCustomerGroupMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(protected CustomerGroupRepository $customerGroupRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }
        $this->assertPermission($admin);

        $indices = $this->resolveIndices($data, $context);
        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.group.mass-delete-indices-required'), 422);
        }

        $deleted = [];
        $skipped = [];

        foreach ($indices as $idx) {
            $id = (int) $idx;
            $group = CustomerGroup::find($id);
            if (! $group) {
                continue;
            }

            if (! (int) $group->is_user_defined) {
                $skipped[] = ['id' => $id, 'reason' => __('bagistoapi::app.admin.customer.group.is-system')];

                continue;
            }

            if ($group->customers()->count() > 0) {
                $skipped[] = ['id' => $id, 'reason' => __('bagistoapi::app.admin.customer.group.has-customers')];

                continue;
            }

            try {
                Event::dispatch('customer.customer_group.delete.before', $id);
                $this->customerGroupRepository->delete($id);
                Event::dispatch('customer.customer_group.delete.after', $id);
                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                $skipped[] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        $result = new AdminCustomerGroupMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->skipped = $skipped;
        $result->message = __('bagistoapi::app.admin.customer.group.mass-delete-success');

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
        if (! in_array('customers.groups.delete', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }
    }

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminCustomerGroupMassDeleteInput && ! empty($data->indices)) {
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
