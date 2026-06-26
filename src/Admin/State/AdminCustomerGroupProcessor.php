<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGroupCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGroupUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGroup;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Rules\Code;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Customer\Repositories\CustomerGroupRepository;

class AdminCustomerGroupProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminCustomerGroupItemProvider $itemProvider,
        protected CustomerGroupRepository $customerGroupRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminCustomerGroupUpdateInput) {
            $this->assertPermission($admin, 'customers.groups.delete');
            $id = (int) basename((string) ($data->id ?? ($context['args']['input']['id'] ?? '')));

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminCustomerGroupCreateInput
            || ($data instanceof AdminCustomerGroup && $operation instanceof Post)) {
            $this->assertPermission($admin, 'customers.groups.create');

            return $this->handleCreate($this->resolveInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminCustomerGroupUpdateInput
            || ($data instanceof AdminCustomerGroup && $operation instanceof Put)) {
            $this->assertPermission($admin, 'customers.groups.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) ($data->id ?? ($context['args']['input']['id'] ?? ''))));

            return $this->handleUpdate($id, $this->resolveInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'customers.groups.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminCustomerGroup
    {
        $v = Validator::make($input, [
            'code' => ['required', 'unique:customer_groups,code', new Code],
            'name' => ['required', 'string'],
        ]);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $data = [
            'code'            => $input['code'],
            'name'            => $input['name'],
            'is_user_defined' => 1,
        ];

        Event::dispatch('customer.customer_group.create.before');

        $group = $this->customerGroupRepository->create($data);

        Event::dispatch('customer.customer_group.create.after', $group);

        return $this->itemProvider->mapToDto(CustomerGroup::find($group->id));
    }

    protected function handleUpdate(int $id, array $input): AdminCustomerGroup
    {
        $existing = CustomerGroup::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.group.not-found'));
        }

        $isSystem = ! (int) $existing->is_user_defined;

        if ($isSystem) {
            if (array_key_exists('code', $input) && $input['code'] !== null && $input['code'] !== $existing->code) {
                throw new InvalidInputException(__('bagistoapi::app.admin.customer.group.system-code-immutable'), 422);
            }
            if (array_key_exists('is_user_defined', $input) && $input['is_user_defined'] !== null && (int) $input['is_user_defined'] !== (int) $existing->is_user_defined) {
                throw new InvalidInputException(__('bagistoapi::app.admin.customer.group.system-flag-immutable'), 422);
            }
        }

        $rules = [
            'code' => ['sometimes', 'required', 'unique:customer_groups,code,'.$id, new Code],
            'name' => ['sometimes', 'required', 'string'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $patch = [];
        if (array_key_exists('name', $input) && $input['name'] !== null) {
            $patch['name'] = $input['name'];
        }
        if (! $isSystem && array_key_exists('code', $input) && $input['code'] !== null) {
            $patch['code'] = $input['code'];
        }

        Event::dispatch('customer.customer_group.update.before', $id);

        if (! empty($patch)) {
            $this->customerGroupRepository->update($patch, $id);
        }

        $group = $existing->fresh();

        Event::dispatch('customer.customer_group.update.after', $group);

        return $this->itemProvider->mapToDto($group);
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminCustomerGroup
    {
        $existing = CustomerGroup::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.group.not-found'));
        }

        if (! (int) $existing->is_user_defined) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.group.is-system'), 400);
        }

        if ($existing->customers()->count() > 0) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.group.has-customers'), 400);
        }

        $snapshot = $asResource ? $this->itemProvider->mapToDto($existing) : null;

        Event::dispatch('customer.customer_group.delete.before', $id);

        $this->customerGroupRepository->delete($id);

        Event::dispatch('customer.customer_group.delete.after', $id);

        if ($asResource && $snapshot instanceof AdminCustomerGroup) {
            $snapshot->message = __('bagistoapi::app.admin.customer.group.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.customer.group.deleted')];
    }

    protected function resolveInput(mixed $data, array $context, bool $isGraphQL): array
    {
        if ($isGraphQL) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->normalizeKeys($rawArgs);
        }

        return request()->all();
    }

    protected function normalizeKeys(array $args): array
    {
        $map = [
            'isUserDefined' => 'is_user_defined',
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
