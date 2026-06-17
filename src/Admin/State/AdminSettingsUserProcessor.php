<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsUserCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsUserUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsUser;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\User\Models\Admin;

/**
 * Handles POST / PUT / DELETE for the AdminSettingsUser resource.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\UserController.
 *
 * Notes:
 *  - image (avatar) upload is deferred — only a string path is accepted in v1.
 *  - Permission resolution mirrors AdminSettingsLocaleProcessor — reads
 *    role->permission_type / role->permissions directly, never calls bouncer().
 *  - Delete refuses if caller is deleting themselves (400) or if this is
 *    the last admin (400).
 *  - Password is required on create; optional on update (re-hashed via Hash::make()
 *    when present, otherwise the existing hash is preserved).
 */
class AdminSettingsUserProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminSettingsUserItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsUserUpdateInput) {
            $this->assertPermission($admin, 'settings.users.users.delete');
            $id = (int) basename((string) $this->resolveUpdateId($data, $context));

            return $this->handleDelete($id, (int) $admin->id);
        }

        if ($data instanceof AdminSettingsUserCreateInput
            || ($data instanceof AdminSettingsUser && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.users.users.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminSettingsUserUpdateInput
            || ($data instanceof AdminSettingsUser && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.users.users.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.users.users.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id, (int) $admin->id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminSettingsUser
    {
        $payload = $this->normaliseCreatePayload($input);

        $this->validateCreatePayload($payload);

        Event::dispatch('user.admin.create.before');

        $admin = new Admin;
        $admin->name = $payload['name'];
        $admin->email = $payload['email'];
        $admin->password = Hash::make($payload['password']);
        $admin->role_id = $payload['role_id'];
        $admin->status = $payload['status'] ?? 1;
        if (array_key_exists('image', $payload) && (is_string($payload['image']) || $payload['image'] === null)) {
            $admin->image = $payload['image'];
        }
        $admin->save();

        Event::dispatch('user.admin.create.after', $admin);

        return $this->fetchAndMap((int) $admin->id);
    }

    protected function handleUpdate(int $id, array $input): AdminSettingsUser
    {
        $existing = Admin::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.user.not-found'));
        }

        $payload = $this->normaliseUpdatePayload($input, $existing);

        $this->validateUpdatePayload($payload, $id, $input);

        Event::dispatch('user.admin.update.before', $id);

        $existing->name = $payload['name'];
        $existing->email = $payload['email'];
        $existing->role_id = $payload['role_id'];
        $existing->status = $payload['status'];

        if (! empty($input['password'])) {
            $existing->password = Hash::make((string) $input['password']);
        }

        if (array_key_exists('image', $input) && (is_string($input['image']) || $input['image'] === null)) {
            $existing->image = $input['image'];
        }

        $existing->save();

        Event::dispatch('user.admin.update.after', $existing);

        return $this->fetchAndMap($id);
    }

    protected function handleDelete(int $id, int $callerAdminId): array
    {
        $existing = Admin::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.user.not-found'));
        }

        if ($id === $callerAdminId) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.user.cannot-delete-self'), 400);
        }

        if (Admin::count() <= 1) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.user.cannot-delete-last-admin'), 400);
        }

        try {
            Event::dispatch('user.admin.delete.before', $id);

            $existing->delete();

            Event::dispatch('user.admin.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.user.delete-failed'),
                500,
            );
        }

        return ['message' => __('bagistoapi::app.admin.settings.user.deleted')];
    }

    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'name'     => ['required', 'string'],
            'email'    => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id'  => ['required', 'integer', 'exists:roles,id'],
            'status'   => ['nullable', 'in:0,1'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function validateUpdatePayload(array $input, int $excludeId, array $rawInput): void
    {
        $rules = [
            'name'    => ['required', 'string'],
            'email'   => ['required', 'email', 'unique:admins,email,'.$excludeId],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'status'  => ['nullable', 'in:0,1'],
        ];

        if (! empty($rawInput['password'])) {
            $rules['password'] = ['string', 'min:6'];
            $input['password'] = $rawInput['password'];
        }

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.user.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.user.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsUserCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsUserUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsUserUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Map GraphQL camelCase args to snake_case the validator expects.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'roleId' => 'role_id',
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

    protected function normaliseCreatePayload(array $input): array
    {
        $out = [
            'name'     => isset($input['name']) ? (string) $input['name'] : null,
            'email'    => isset($input['email']) ? (string) $input['email'] : null,
            'password' => isset($input['password']) ? (string) $input['password'] : null,
            'role_id'  => isset($input['role_id']) ? (int) $input['role_id'] : null,
            'status'   => isset($input['status']) ? (int) $input['status'] : 1,
        ];

        if (array_key_exists('image', $input) && (is_string($input['image']) || $input['image'] === null)) {
            $out['image'] = $input['image'];
        }

        return $out;
    }

    protected function normaliseUpdatePayload(array $input, Admin $existing): array
    {
        return [
            'name'    => isset($input['name']) && $input['name'] !== '' && $input['name'] !== null
                ? (string) $input['name']
                : $existing->name,
            'email'   => isset($input['email']) && $input['email'] !== '' && $input['email'] !== null
                ? (string) $input['email']
                : $existing->email,
            'role_id' => isset($input['role_id']) && $input['role_id'] !== '' && $input['role_id'] !== null
                ? (int) $input['role_id']
                : $existing->role_id,
            'status'  => isset($input['status']) && $input['status'] !== '' && $input['status'] !== null
                ? (int) $input['status']
                : $existing->status,
        ];
    }

    protected function fetchAndMap(int $id): AdminSettingsUser
    {
        $fresh = Admin::with('role')->find($id);

        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $fresh);
    }
}
