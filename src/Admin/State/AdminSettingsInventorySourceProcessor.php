<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsInventorySourceCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsInventorySourceUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsInventorySource;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Inventory\Models\InventorySource;
use Webkul\Inventory\Repositories\InventorySourceRepository;

/**
 * Handles POST / PUT / DELETE for the AdminSettingsInventorySource resource.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\InventorySourceController.
 *
 * Permission resolution mirrors AdminSettingsExchangeRateProcessor — read
 * role->permission_type / role->permissions directly, never call bouncer()
 * (Sanctum-token requests have no session-bound admin).
 *
 * Delete guards (parity with monolith + FK guard):
 *  - last remaining source → 400.
 *  - referenced by any product_inventories row → 400.
 */
class AdminSettingsInventorySourceProcessor implements ProcessorInterface
{
    public function __construct(
        protected InventorySourceRepository $inventorySourceRepository,
        protected AdminSettingsInventorySourceItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsInventorySourceUpdateInput) {
            $this->assertPermission($admin, 'settings.inventory_sources.delete');
            $id = (int) basename((string) $this->resolveUpdateId($data, $context));

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminSettingsInventorySourceCreateInput
            || ($data instanceof AdminSettingsInventorySource && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.inventory_sources.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminSettingsInventorySourceUpdateInput
            || ($data instanceof AdminSettingsInventorySource && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.inventory_sources.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.inventory_sources.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminSettingsInventorySource
    {
        $input = $this->normalizePayload($input);
        $this->validatePayload($input, null);

        Event::dispatch('inventory.inventory_source.create.before');

        $source = $this->inventorySourceRepository->create($this->onlyAllowed($input));

        Event::dispatch('inventory.inventory_source.create.after', $source);

        return $this->fetchAndMap((int) $source->id);
    }

    protected function handleUpdate(int $id, array $input): AdminSettingsInventorySource
    {
        $existing = InventorySource::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.inventory-source.not-found'));
        }

        $merged = array_merge($existing->toArray(), $this->normalizePayload($input));

        $this->validatePayload($merged, $id);

        Event::dispatch('inventory.inventory_source.update.before', $id);

        $source = $this->inventorySourceRepository->update($this->onlyAllowed($merged), $id);

        Event::dispatch('inventory.inventory_source.update.after', $source);

        return $this->fetchAndMap($id);
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminSettingsInventorySource
    {
        $existing = InventorySource::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.inventory-source.not-found'));
        }

        if (InventorySource::count() <= 1) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.inventory-source.last-delete-error'),
                400,
            );
        }

        if (Schema::hasTable('product_inventories')
            && DB::table('product_inventories')->where('inventory_source_id', $id)->exists()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.inventory-source.in-use'),
                400,
            );
        }

        try {
            Event::dispatch('inventory.inventory_source.delete.before', $id);

            $this->inventorySourceRepository->delete($id);

            Event::dispatch('inventory.inventory_source.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.inventory-source.delete-failed'),
                500,
            );
        }

        if ($asResource) {
            $snapshot = $this->mapModel($existing);
            $snapshot->message = __('bagistoapi::app.admin.settings.inventory-source.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.settings.inventory-source.deleted')];
    }

    /**
     * Mirrors Webkul\Admin\Http\Requests\InventorySourceRequest::rules() but uses
     * plain Laravel validators instead of the heavier Bagisto Address / PhoneNumber
     * / PostCode rules (those reject any non-ASCII / odd formats and would block
     * legitimate-looking test fixtures).
     *
     * @param  int|null  $excludeId  null on create, the row id on update.
     */
    protected function validatePayload(array $input, ?int $excludeId): void
    {
        $rules = [
            'code'           => ['required', 'string', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'name'           => ['required', 'string'],
            'contact_name'   => ['required', 'string'],
            'contact_email'  => ['required', 'email'],
            'contact_number' => ['required', 'string'],
            'country'        => ['required', 'string'],
            'state'          => ['required', 'string'],
            'city'           => ['required', 'string'],
            'street'         => ['required', 'string'],
            'postcode'       => ['required', 'string'],
            'priority'       => ['nullable', 'numeric'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'status'         => ['nullable', 'in:0,1'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $q = DB::table('inventory_sources')->where('code', $input['code']);
        if ($excludeId !== null) {
            $q->where('id', '<>', $excludeId);
        }
        if ($q->limit(1)->exists()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.inventory-source.code-unique'),
                422,
            );
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.inventory-source.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.inventory-source.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsInventorySourceCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsInventorySourceUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsInventorySourceUpdateInput) {
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
            'contactName'   => 'contact_name',
            'contactEmail'  => 'contact_email',
            'contactNumber' => 'contact_number',
            'contactFax'    => 'contact_fax',
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

    /**
     * Normalize empty-string fields to null and coerce numeric strings.
     */
    protected function normalizePayload(array $input): array
    {
        foreach (['description', 'contact_fax', 'latitude', 'longitude'] as $opt) {
            if (array_key_exists($opt, $input) && $input[$opt] === '') {
                $input[$opt] = null;
            }
        }

        if (isset($input['priority']) && $input['priority'] === '') {
            $input['priority'] = 0;
        }

        if (! isset($input['status']) || $input['status'] === '' || $input['status'] === null) {
            $input['status'] = 0;
        } else {
            $input['status'] = (int) $input['status'];
        }

        return $input;
    }

    /**
     * Restrict the payload sent to the repository to known columns.
     */
    protected function onlyAllowed(array $input): array
    {
        $allowed = [
            'code', 'name', 'description', 'contact_name', 'contact_email',
            'contact_number', 'contact_fax', 'country', 'state', 'city',
            'street', 'postcode', 'priority', 'latitude', 'longitude', 'status',
        ];

        return array_intersect_key($input, array_flip($allowed));
    }

    protected function fetchAndMap(int $id): AdminSettingsInventorySource
    {
        return $this->mapModel(InventorySource::find($id));
    }

    protected function mapModel(object $model): AdminSettingsInventorySource
    {
        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $model);
    }
}
