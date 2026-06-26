<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsRoleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsRoleUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsRole;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\User\Models\Role;
use Webkul\User\Repositories\RoleRepository;

/**
 * Handles POST, PUT, DELETE on AdminSettingsRole. Mirrors core
 * Webkul\Admin\Http\Controllers\Settings\RoleController.
 *
 * Delete guards:
 *   1. Any admin (admins.role_id) references this role → HTTP 400.
 *   2. This is the only remaining role → HTTP 400.
 */
class AdminSettingsRoleProcessor implements ProcessorInterface
{
    public function __construct(
        protected RoleRepository $roleRepository,
        protected AdminSettingsRoleItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsRoleUpdateInput) {
            $this->assertPermission($admin, 'settings.roles.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminSettingsRoleCreateInput
            || ($data instanceof AdminSettingsRole && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.roles.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminSettingsRoleUpdateInput
            || ($data instanceof AdminSettingsRole && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.roles.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.roles.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminSettingsRole
    {
        $this->validatePayload($input);

        $payload = $this->filterRepositoryPayload($input);

        \Event::dispatch('user.role.create.before');

        $role = $this->roleRepository->create($payload);

        \Event::dispatch('user.role.create.after', $role);

        return $this->itemProvider->mapToDtoPublic(Role::find($role->id));
    }

    protected function handleUpdate(int $id, array $input): AdminSettingsRole
    {
        $role = Role::find($id);
        if (! $role) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.role.not-found'));
        }

        $this->validatePayload($input);

        $payload = $this->filterRepositoryPayload($input);

        if (($payload['permission_type'] ?? null) === 'all') {
            $payload['permissions'] = [];
        }

        \Event::dispatch('user.role.update.before', $id);

        $this->roleRepository->update($payload, $id);

        \Event::dispatch('user.role.update.after', Role::find($id));

        return $this->itemProvider->mapToDtoPublic(Role::find($id));
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminSettingsRole
    {
        $role = Role::find($id);
        if (! $role) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.role.not-found'));
        }

        if (DB::table('admins')->where('role_id', $id)->exists()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.role.cannot-delete-in-use'),
                400,
            );
        }

        if (Role::count() <= 1) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.role.cannot-delete-last-role'),
                400,
            );
        }

        try {
            \Event::dispatch('user.role.delete.before', $id);
            $this->roleRepository->delete($id);
            \Event::dispatch('user.role.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.role.delete-failed'),
                500,
            );
        }

        if ($asResource) {
            $snapshot = $this->itemProvider->mapToDtoPublic($role);
            $snapshot->message = __('bagistoapi::app.admin.settings.role.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.settings.role.deleted')];
    }

    protected function validatePayload(array $input): void
    {
        $rules = [
            'name'            => ['required', 'string'],
            'description'     => ['required', 'string'],
            'permission_type' => ['required', 'in:all,custom'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        if (($input['permission_type'] ?? null) === 'custom') {
            $perms = $input['permissions'] ?? null;
            if (! is_array($perms) || count($perms) === 0) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.settings.role.permissions-required'),
                    422,
                );
            }
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.role.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.role.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsRoleCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsRoleUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsRoleUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'permissionType' => 'permission_type',
        ];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $snakeKey = $camelToSnake[$key] ?? $key;
            $result[$snakeKey] = $value;
        }

        foreach (get_object_vars($dto) as $key => $value) {
            if ($value !== null && ! array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function filterRepositoryPayload(array $input): array
    {
        unset($input['id']);

        $filtered = array_intersect_key($input, array_flip([
            'name',
            'description',
            'permission_type',
            'permissions',
        ]));

        if (array_key_exists('permissions', $filtered)) {
            if (! is_array($filtered['permissions'])) {
                $filtered['permissions'] = [];
            }
        }

        return $filtered;
    }
}
