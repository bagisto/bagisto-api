<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
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
use Webkul\User\Models\Role;
use Webkul\User\Repositories\AdminRepository;

class AdminSettingsUserProcessor implements ProcessorInterface
{
    /**
     * Create a new admin settings user processor instance.
     */
    public function __construct(
        protected AdminSettingsUserItemProvider $itemProvider,
        protected AdminRepository $adminRepository,
    ) {}

    /**
     * Dispatch an admin user create, update or delete operation after checking the calling admin's permission.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if (
            $isGraphQL
            && $operation->getName() === 'delete'
            && $data instanceof AdminSettingsUserUpdateInput
        ) {
            $this->assertPermission($admin, 'settings.users.delete');
            $id = (int) basename((string) $this->resolveUpdateId($data, $context));

            return $this->handleDelete($admin, $id, true);
        }

        if (
            $data instanceof AdminSettingsUserCreateInput
            || (
                $data instanceof AdminSettingsUser
                && $operation instanceof Post
            )
        ) {
            $this->assertPermission($admin, 'settings.users.create');

            return $this->handleCreate($admin, $this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if (
            $data instanceof AdminSettingsUserUpdateInput
            || (
                $data instanceof AdminSettingsUser
                && $operation instanceof Put
            )
        ) {
            $this->assertPermission($admin, 'settings.users.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($admin, $id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.users.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($admin, $id);
        }

        return null;
    }

    /**
     * Validate the payload and create an admin user with a role the calling admin is allowed to grant.
     */
    protected function handleCreate(object $admin, array $input): AdminSettingsUser
    {
        $payload = $this->normaliseCreatePayload($input);

        $this->validateCreatePayload($payload);

        $this->assertCanGrantRole($admin, Role::find($payload['role_id']));

        Event::dispatch('user.admin.create.before');

        $user = new Admin;
        $user->name = $payload['name'];
        $user->email = $payload['email'];
        $user->password = Hash::make($payload['password']);
        $user->role_id = $payload['role_id'];
        $user->status = $payload['status'] ?? 1;

        if (
            array_key_exists('image', $payload)
            && (
                is_string($payload['image'])
                || $payload['image'] === null
            )
        ) {
            $user->image = $payload['image'];
        }

        $user->save();

        Event::dispatch('user.admin.create.after', $user);

        return $this->fetchAndMap((int) $user->id);
    }

    /**
     * Validate the payload and update an existing admin user, re-hashing the password only when one is given.
     */
    protected function handleUpdate(object $admin, int $id, array $input): AdminSettingsUser
    {
        $existing = Admin::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.user.not-found'));
        }

        $payload = $this->normaliseUpdatePayload($input, $existing);

        $this->validateUpdatePayload($payload, $id, $input);

        $this->assertCanGrantRole($admin, $existing->role);

        $this->assertCanGrantRole($admin, Role::find($payload['role_id']));

        $this->assertNotLastAllAccessAdmin($existing, $payload);

        Event::dispatch('user.admin.update.before', $id);

        $existing->name = $payload['name'];
        $existing->email = $payload['email'];
        $existing->role_id = $payload['role_id'];
        $existing->status = $payload['status'];

        $passwordChanged = ! empty($input['password']);

        if ($passwordChanged) {
            $existing->password = Hash::make((string) $input['password']);
        }

        if (
            array_key_exists('image', $input)
            && (
                is_string($input['image'])
                || $input['image'] === null
            )
        ) {
            $existing->image = $input['image'];
        }

        $existing->save();

        if ($passwordChanged) {
            Event::dispatch('admin.password.update.after', $existing);
        }

        Event::dispatch('user.admin.update.after', $existing);

        return $this->fetchAndMap($id);
    }

    /**
     * Refuse deactivating or re-roling the last all-access admin.
     */
    protected function assertNotLastAllAccessAdmin(Admin $existing, array $payload): void
    {
        if (($existing->role?->permission_type ?? null) !== 'all') {
            return;
        }

        $isDeactivated = (int) $payload['status'] === 0 && (bool) $existing->status;

        if (
            $isDeactivated
            && $this->adminRepository->countAdminsWithAllAccessAndActiveStatus() === 1
        ) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.user.cannot-change', ['name' => 'status']),
                400,
            );
        }

        $isRoleChanged = (int) $payload['role_id'] !== (int) $existing->role_id;

        if (
            $isRoleChanged
            && $this->adminRepository->countAdminsWithAllAccess() === 1
        ) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.user.cannot-change', ['name' => 'role']),
                400,
            );
        }
    }

    /**
     * Refuse assigning, or acting on a user with, a role that holds a permission the calling admin lacks.
     */
    protected function assertCanGrantRole(object $admin, ?Role $role): void
    {
        if (! AdminAuthHelper::canGrantRole($admin, $role)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.user.no-permission'));
        }
    }

    /**
     * Delete an admin user other than the caller and the last admin, returning a message or a resource snapshot.
     */
    protected function handleDelete(object $admin, int $id, bool $asResource = false): array|AdminSettingsUser
    {
        $existing = Admin::with('role')->find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.user.not-found'));
        }

        if ($id === (int) $admin->id) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.user.cannot-delete-self'), 400);
        }

        if (Admin::count() <= 1) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.user.cannot-delete-last-admin'), 400);
        }

        $this->assertCanGrantRole($admin, $existing->role);

        try {
            Event::dispatch('user.admin.delete.before', $id);

            $existing->delete();

            Event::dispatch('user.admin.delete.after', $id);
        } catch (\Throwable $exception) {
            report($exception);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.user.delete-failed'),
                500,
            );
        }

        if ($asResource) {
            $snapshot = $this->mapModel($existing);
            $snapshot->message = __('bagistoapi::app.admin.settings.user.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.settings.user.deleted')];
    }

    /**
     * Validate the create payload, requiring a unique email, a password and an existing role.
     */
    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'status' => ['nullable', 'in:0,1'],
        ];

        $value = Validator::make($input, $rules);
        if ($value->fails()) {
            throw new InvalidInputException($value->errors()->first(), 422);
        }
    }

    /**
     * Validate the update payload, checking the password only when the raw input carries one.
     */
    protected function validateUpdatePayload(array $input, int $excludeId, array $rawInput): void
    {
        $rules = [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:admins,email,'.$excludeId],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'status' => ['nullable', 'in:0,1'],
        ];

        if (! empty($rawInput['password'])) {
            $rules['password'] = ['string', 'min:6'];
            $input['password'] = $rawInput['password'];
        }

        $value = Validator::make($input, $rules);
        if ($value->fails()) {
            throw new InvalidInputException($value->errors()->first(), 422);
        }
    }

    /**
     * Refuse the operation unless the calling admin's role grants the given permission.
     */
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

        if (
            ! in_array($permission, $perms, true)
            && ! in_array('*', $perms, true)
        ) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.user.no-permission'));
        }
    }

    /**
     * Resolve the create input from GraphQL arguments or the REST request body.
     */
    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if (
            $isGraphQL
            && $data instanceof AdminSettingsUserCreateInput
        ) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Resolve the admin user identifier from the update input or the GraphQL arguments.
     */
    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if (
            $data instanceof AdminSettingsUserUpdateInput
            && $data->id
        ) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    /**
     * Resolve the update input from GraphQL arguments or the REST request body.
     */
    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if (
            $isGraphQL
            && $data instanceof AdminSettingsUserUpdateInput
        ) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Map GraphQL camelCase arguments and DTO properties to the snake_case payload the validator expects.
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
            if (
                $value !== null
                && ! array_key_exists($key, $result)
            ) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Cast the create input to typed fields, defaulting the status to active.
     */
    protected function normaliseCreatePayload(array $input): array
    {
        $out = [
            'name' => isset($input['name']) ? (string) $input['name'] : null,
            'email' => isset($input['email']) ? (string) $input['email'] : null,
            'password' => isset($input['password']) ? (string) $input['password'] : null,
            'role_id' => isset($input['role_id']) ? (int) $input['role_id'] : null,
            'status' => isset($input['status']) ? (int) $input['status'] : 1,
        ];

        if (
            array_key_exists('image', $input)
            && (
                is_string($input['image'])
                || $input['image'] === null
            )
        ) {
            $out['image'] = $input['image'];
        }

        return $out;
    }

    /**
     * Build the update payload, falling back to the existing admin's values for empty fields.
     */
    protected function normaliseUpdatePayload(array $input, Admin $existing): array
    {
        return [
            'name' => isset($input['name']) && $input['name'] !== '' && $input['name'] !== null
                ? (string) $input['name']
                : $existing->name,
            'email' => isset($input['email']) && $input['email'] !== '' && $input['email'] !== null
                ? (string) $input['email']
                : $existing->email,
            'role_id' => isset($input['role_id']) && $input['role_id'] !== '' && $input['role_id'] !== null
                ? (int) $input['role_id']
                : $existing->role_id,
            'status' => isset($input['status']) && $input['status'] !== '' && $input['status'] !== null
                ? (int) $input['status']
                : $existing->status,
        ];
    }

    /**
     * Load the admin user with its role and map it to the API resource.
     */
    protected function fetchAndMap(int $id): AdminSettingsUser
    {
        return $this->mapModel(Admin::with('role')->find($id));
    }

    /**
     * Map an admin model to the API resource through the item provider's protected mapper.
     */
    protected function mapModel(object $model): AdminSettingsUser
    {
        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $model);
    }
}
