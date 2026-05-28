<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Attribute\Models\AttributeFamily;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\BagistoApi\Admin\Dto\AdminAttributeFamilyCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminAttributeFamilyUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminAttributeFamily;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Rules\Code;

/**
 * Handles POST, PUT, DELETE on the AdminAttributeFamily resource (Phase 4 — CRUD).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Catalog\AttributeFamilyController:
 *   - store()   validation + events + AttributeFamilyRepository::create
 *   - update()  validation + events + AttributeFamilyRepository::update
 *   - destroy() last-family / products-attached guards + events + delete
 *
 * Permission resolution mirrors AdminAttributeProcessor: read role->permission_type
 * / role->permissions directly; never call bouncer() (Sanctum-token requests have
 * no session-bound admin).
 */
class AdminAttributeFamilyProcessor implements ProcessorInterface
{
    public function __construct(
        protected AttributeFamilyRepository $attributeFamilyRepository,
        protected AdminAttributeFamilyItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        // ── GraphQL delete — both delete and update use AdminAttributeFamilyUpdateInput;
        //    route by operation name first.
        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminAttributeFamilyUpdateInput) {
            $this->assertPermission($admin, 'catalog.families.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        // ── Create ────────────────────────────────────────────────────────────
        if ($data instanceof AdminAttributeFamilyCreateInput
            || ($data instanceof AdminAttributeFamily && $operation instanceof Post)) {
            $this->assertPermission($admin, 'catalog.families.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        // ── Update ────────────────────────────────────────────────────────────
        if ($data instanceof AdminAttributeFamilyUpdateInput
            || ($data instanceof AdminAttributeFamily && $operation instanceof Put)) {
            $this->assertPermission($admin, 'catalog.families.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        // ── REST Delete ───────────────────────────────────────────────────────
        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'catalog.families.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleCreate(array $input): AdminAttributeFamily
    {
        $this->validateFamilyPayload($input);

        Event::dispatch('catalog.attribute_family.create.before');

        $family = $this->attributeFamilyRepository->create([
            'code'             => $input['code'],
            'name'             => $input['name'],
            'attribute_groups' => $input['attribute_groups'] ?? [],
        ]);

        Event::dispatch('catalog.attribute_family.create.after', $family);

        return $this->fetchAndMap((int) $family->id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Update
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleUpdate(int $id, array $input): AdminAttributeFamily
    {
        $family = AttributeFamily::find($id);
        if (! $family) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.family.not-found'));
        }

        $this->validateFamilyPayload($input, $id);

        Event::dispatch('catalog.attribute_family.update.before', $id);

        $updated = $this->attributeFamilyRepository->update([
            'code'             => $input['code'],
            'name'             => $input['name'],
            'attribute_groups' => $input['attribute_groups'] ?? [],
        ], $id);

        Event::dispatch('catalog.attribute_family.update.after', $updated);

        return $this->fetchAndMap($id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleDelete(int $id): array
    {
        $family = AttributeFamily::find($id);
        if (! $family) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.family.not-found'));
        }

        // Last-family guard — mirrors monolith (returns 400 in core)
        if ($this->attributeFamilyRepository->count() === 1) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.family.last-delete-error'),
                400,
            );
        }

        // Refuse if any product is using this family
        if ($family->products()->count() > 0) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.family.attribute-product-error'),
                400,
            );
        }

        try {
            Event::dispatch('catalog.attribute_family.delete.before', $id);

            $this->attributeFamilyRepository->delete($id);

            Event::dispatch('catalog.attribute_family.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.family.delete-failed'),
                500,
            );
        }

        return ['message' => __('bagistoapi::app.admin.family.deleted')];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validates the family payload. Mirrors the core controller's rules.
     * Passing $id triggers the "unique except this id" form on the code rule.
     */
    protected function validateFamilyPayload(array $input, ?int $excludeId = null): void
    {
        $codeUniqueRule = $excludeId
            ? "unique:attribute_families,code,{$excludeId}"
            : 'unique:attribute_families,code';

        $rules = [
            'code'                      => ['required', $codeUniqueRule, new Code],
            'name'                      => 'required',
            'attribute_groups.*.code'   => 'required',
            'attribute_groups.*.name'   => 'required',
            'attribute_groups.*.column' => 'required|in:1,2',
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Permission helper (mirrors AdminAttributeProcessor — no bouncer() use)
    // ─────────────────────────────────────────────────────────────────────────

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.family.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.family.no-permission'));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Input resolution
    // ─────────────────────────────────────────────────────────────────────────

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminAttributeFamilyCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminAttributeFamilyUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminAttributeFamilyUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Map GraphQL camelCase args to the snake_case form the validator + repository expect.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'attributeGroups' => 'attribute_groups',
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

    // ─────────────────────────────────────────────────────────────────────────
    // Output mapping
    // ─────────────────────────────────────────────────────────────────────────

    protected function fetchAndMap(int $id): AdminAttributeFamily
    {
        $fresh = AttributeFamily::with(['attribute_groups.custom_attributes'])->find($id);

        // Delegate to the item provider's mapping logic so the response shape is identical
        // to GET /api/admin/catalog/families/{id}.
        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $fresh);
    }
}
