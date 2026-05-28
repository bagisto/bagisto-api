<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxCategoryCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxCategoryUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxCategory;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Tax\Models\TaxCategory;
use Webkul\Tax\Repositories\TaxCategoryRepository;

/**
 * Handles POST, PUT, DELETE on AdminSettingsTaxCategory.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\Tax\TaxCategoryController:
 *   store / update / destroy. Events fired:
 *     tax.category.create.before / after
 *     tax.category.update.before / after
 *     tax.category.delete.before / after
 *
 * Permission resolution: Sanctum pattern — read role->permission_type /
 * role->permissions directly. Never calls bouncer().
 *
 * Delete guard (parity with monolith): refuses with HTTP 400 if any tax_rates
 * remain attached to the category.
 */
class AdminSettingsTaxCategoryProcessor implements ProcessorInterface
{
    public function __construct(
        protected TaxCategoryRepository $taxCategoryRepository,
        protected AdminSettingsTaxCategoryItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        // GraphQL delete — update + delete both carry AdminSettingsTaxCategoryUpdateInput; route by name.
        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsTaxCategoryUpdateInput) {
            $this->assertPermission($admin, 'settings.taxes.tax_categories.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        // Create
        if ($data instanceof AdminSettingsTaxCategoryCreateInput
            || ($data instanceof AdminSettingsTaxCategory && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.taxes.tax_categories.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        // Update
        if ($data instanceof AdminSettingsTaxCategoryUpdateInput
            || ($data instanceof AdminSettingsTaxCategory && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.taxes.tax_categories.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        // REST Delete
        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.taxes.tax_categories.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    protected function handleCreate(array $input): AdminSettingsTaxCategory
    {
        $this->validateCreatePayload($input);

        Event::dispatch('tax.category.create.before');

        $payload = $this->filterRepositoryPayload($input);
        $taxCategory = $this->taxCategoryRepository->create($payload);

        // Re-fetch as Eloquent (the repository returns the contract interface).
        $taxCategory = TaxCategory::find($taxCategory->id);

        $taxCategory->tax_rates()->sync($input['taxrates'] ?? []);

        Event::dispatch('tax.category.create.after', $taxCategory);

        return $this->itemProvider->mapToDtoPublic($taxCategory->fresh(['tax_rates']));
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    protected function handleUpdate(int $id, array $input): AdminSettingsTaxCategory
    {
        $taxCategory = TaxCategory::find($id);
        if (! $taxCategory) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.tax-category.not-found'));
        }

        $this->validateUpdatePayload($input, $id);

        Event::dispatch('tax.category.update.before', $id);

        $payload = $this->filterRepositoryPayload($input);
        $this->taxCategoryRepository->update($payload, $id);

        $taxCategory = TaxCategory::find($id);

        if (array_key_exists('taxrates', $input)) {
            $taxCategory->tax_rates()->sync($input['taxrates'] ?? []);
        }

        Event::dispatch('tax.category.update.after', $taxCategory);

        return $this->itemProvider->mapToDtoPublic($taxCategory->fresh(['tax_rates']));
    }

    // ─── Delete ──────────────────────────────────────────────────────────────

    protected function handleDelete(int $id): array
    {
        $taxCategory = TaxCategory::find($id);
        if (! $taxCategory) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.tax-category.not-found'));
        }

        if ($taxCategory->tax_rates()->count() > 0) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.tax-category.cannot-delete-in-use'),
                400,
            );
        }

        Event::dispatch('tax.category.delete.before', $id);

        try {
            // Detach pivot defensively (no rows expected here — guard above).
            $taxCategory->tax_rates()->detach();
            $taxCategory->delete();
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.tax-category.delete-failed'),
                500,
            );
        }

        Event::dispatch('tax.category.delete.after', $id);

        return ['message' => __('bagistoapi::app.admin.settings.tax-category.deleted')];
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'code'        => ['required', 'string'],
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
            'taxrates'    => ['required', 'array'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        if (DB::table('tax_categories')->where('code', $input['code'])->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.tax-category.code-unique'), 422);
        }
    }

    protected function validateUpdatePayload(array $input, int $id): void
    {
        $rules = [
            'code'        => ['required', 'string'],
            'name'        => ['required', 'string'],
            'description' => ['required', 'string'],
            'taxrates'    => ['required', 'array'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        if (DB::table('tax_categories')->where('code', $input['code'])->where('id', '!=', $id)->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.tax-category.code-unique'), 422);
        }
    }

    // ─── Permissions ─────────────────────────────────────────────────────────

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.tax-category.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.tax-category.no-permission'));
        }
    }

    // ─── Input resolution ────────────────────────────────────────────────────

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsTaxCategoryCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsTaxCategoryUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsTaxCategoryUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Normalise DTO + raw GraphQL args into the snake_case shape the validator
     * and repository expect. (No camelCase mapping needed — all current fields
     * are already snake_case or single-word.)
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        foreach ($rawArgs as $key => $value) {
            if ($value === null) {
                continue;
            }
            $result[$key] = $value;
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

        return array_intersect_key($input, array_flip([
            'code',
            'name',
            'description',
        ]));
    }
}
