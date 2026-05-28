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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxRateCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxRateUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxRate;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Tax\Models\TaxRate;
use Webkul\Tax\Repositories\TaxRateRepository;

/**
 * Handles POST / PUT / DELETE for AdminSettingsTaxRate.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\Tax\TaxRateController.
 *
 * is_zip conditional validation:
 *   - is_zip = false → zip_code required
 *   - is_zip = true  → zip_from + zip_to required
 *
 * Permission resolution mirrors AdminSettingsExchangeRateProcessor (Sanctum
 * pattern — read role->permission_type / role->permissions directly, never
 * call bouncer()).
 */
class AdminSettingsTaxRateProcessor implements ProcessorInterface
{
    public function __construct(
        protected TaxRateRepository $taxRateRepository,
        protected AdminSettingsTaxRateItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        // GraphQL delete reuses AdminSettingsTaxRateUpdateInput — route first by op name.
        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsTaxRateUpdateInput) {
            $this->assertPermission($admin, 'settings.taxes.tax_rates.delete');
            $id = (int) basename((string) $this->resolveUpdateId($data, $context));

            return $this->handleDelete($id);
        }

        // Create
        if ($data instanceof AdminSettingsTaxRateCreateInput
            || ($data instanceof AdminSettingsTaxRate && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.taxes.tax_rates.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        // Update
        if ($data instanceof AdminSettingsTaxRateUpdateInput
            || ($data instanceof AdminSettingsTaxRate && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.taxes.tax_rates.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        // REST Delete
        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.taxes.tax_rates.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create / Update / Delete
    // ─────────────────────────────────────────────────────────────────────────

    protected function handleCreate(array $input): AdminSettingsTaxRate
    {
        $payload = $this->normalisePayload($input);
        $this->validateCreatePayload($payload);

        Event::dispatch('tax.rate.create.before');

        $rate = $this->taxRateRepository->create($this->onlyFillable($payload));

        Event::dispatch('tax.rate.create.after', $rate);

        return $this->fetchAndMap((int) $rate->id);
    }

    protected function handleUpdate(int $id, array $input): AdminSettingsTaxRate
    {
        $existing = TaxRate::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.tax-rate.not-found'));
        }

        // Merge with existing values so partial updates work.
        $existingArr = $existing->toArray();
        $payload = $this->normalisePayload(array_merge([
            'identifier' => $existingArr['identifier'] ?? null,
            'is_zip'     => $existingArr['is_zip'] ?? false,
            'zip_code'   => $existingArr['zip_code'] ?? null,
            'zip_from'   => $existingArr['zip_from'] ?? null,
            'zip_to'     => $existingArr['zip_to'] ?? null,
            'state'      => $existingArr['state'] ?? '',
            'country'    => $existingArr['country'] ?? null,
            'tax_rate'   => $existingArr['tax_rate'] ?? null,
        ], array_filter($input, fn ($v) => $v !== null && $v !== '')));

        // Allow caller to explicitly null out zip_* fields by passing the alternate is_zip
        // mode; normalisePayload + validation will enforce conditional rules below.
        $this->validateUpdatePayload($payload, $id);

        Event::dispatch('tax.rate.update.before', $id);

        $rate = $this->taxRateRepository->update($this->onlyFillable($payload), $id);

        Event::dispatch('tax.rate.update.after', $rate);

        return $this->fetchAndMap($id);
    }

    protected function handleDelete(int $id): array
    {
        $existing = TaxRate::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.tax-rate.not-found'));
        }

        try {
            Event::dispatch('tax.rate.delete.before', $id);

            // Detach pivot rows in tax_categories_tax_rates explicitly. The repo's
            // delete() ultimately deletes the row; the pivot rows reference it via
            // tax_rate_id but the migration does not declare onDelete('cascade'),
            // so we detach here to mirror what BelongsToMany detachment expects.
            DB::table('tax_categories_tax_rates')->where('tax_rate_id', $id)->delete();

            $this->taxRateRepository->delete($id);

            Event::dispatch('tax.rate.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.tax-rate.delete-failed'),
                500,
            );
        }

        return ['message' => __('bagistoapi::app.admin.settings.tax-rate.deleted')];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validation
    // ─────────────────────────────────────────────────────────────────────────

    protected function validateCreatePayload(array $input): void
    {
        $v = Validator::make($input, [
            'identifier' => ['required', 'string', 'unique:tax_rates,identifier'],
            'country'    => ['required', 'string', 'size:2'],
            'state'      => ['nullable', 'string'],
            'tax_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
            'is_zip'     => ['required', 'boolean'],
        ]);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $this->validateConditionalZipRules($input);
    }

    protected function validateUpdatePayload(array $input, int $excludeId): void
    {
        $v = Validator::make($input, [
            'identifier' => ['required', 'string', 'unique:tax_rates,identifier,'.$excludeId],
            'country'    => ['required', 'string', 'size:2'],
            'state'      => ['nullable', 'string'],
            'tax_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
            'is_zip'     => ['required', 'boolean'],
        ]);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        $this->validateConditionalZipRules($input);
    }

    /**
     * is_zip = true → zip_from + zip_to required.
     * is_zip = false → zip_code required.
     */
    protected function validateConditionalZipRules(array $input): void
    {
        $isZip = (bool) ($input['is_zip'] ?? false);

        if ($isZip) {
            if (empty($input['zip_from']) || empty($input['zip_to'])) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.settings.tax-rate.zip-range-required'),
                    422,
                );
            }
        } else {
            if (empty($input['zip_code'])) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.settings.tax-rate.zip-code-required'),
                    422,
                );
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Payload helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalise booleans, clear zip fields for the inactive mode, default state.
     */
    protected function normalisePayload(array $input): array
    {
        $out = $input;

        // Coerce is_zip
        if (array_key_exists('is_zip', $out)) {
            $out['is_zip'] = filter_var($out['is_zip'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($out['is_zip'] === null) {
                $out['is_zip'] = false;
            }
        }

        // state column is NOT NULL in the migration — default to empty string
        if (! isset($out['state']) || $out['state'] === null) {
            $out['state'] = '';
        }

        return $out;
    }

    protected function onlyFillable(array $payload): array
    {
        return array_intersect_key($payload, array_flip([
            'identifier', 'is_zip', 'zip_code', 'zip_from', 'zip_to',
            'state', 'country', 'tax_rate',
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Permission helper
    // ─────────────────────────────────────────────────────────────────────────

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.tax-rate.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.tax-rate.no-permission'));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Input resolution
    // ─────────────────────────────────────────────────────────────────────────

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsTaxRateCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsTaxRateUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsTaxRateUpdateInput) {
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
            'isZip'   => 'is_zip',
            'zipCode' => 'zip_code',
            'zipFrom' => 'zip_from',
            'zipTo'   => 'zip_to',
            'taxRate' => 'tax_rate',
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
    // Output mapping — delegate to item provider
    // ─────────────────────────────────────────────────────────────────────────

    protected function fetchAndMap(int $id): AdminSettingsTaxRate
    {
        $fresh = TaxRate::find($id);

        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $fresh);
    }
}
