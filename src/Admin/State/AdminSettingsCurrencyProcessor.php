<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsCurrencyCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsCurrencyUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsCurrency;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\Currency;
use Webkul\Core\Repositories\CurrencyRepository;

/**
 * Handles POST, PUT, DELETE on AdminSettingsCurrency.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\CurrencyController:
 *   store / update / destroy
 *
 * Permission resolution mirrors AdminCategoryProcessor — read
 * role->permission_type / role->permissions directly. No bouncer() calls.
 *
 * Delete guards (parity with monolith + the extra channel-base check requested
 * for this milestone):
 *   1. Refuse if this is the only currency in the table (HTTP 400).
 *   2. Refuse if any channel references it as channels.base_currency_id (HTTP 400).
 */
class AdminSettingsCurrencyProcessor implements ProcessorInterface
{
    public function __construct(
        protected CurrencyRepository $currencyRepository,
        protected AdminSettingsCurrencyItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        // GraphQL delete — update + delete both carry AdminSettingsCurrencyUpdateInput; route by name.
        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsCurrencyUpdateInput) {
            $this->assertPermission($admin, 'settings.currencies.delete');
            $id = (int) basename($this->resolveUpdateId($data, $context) ?? '0');

            return $this->handleDelete($id);
        }

        // Create
        if ($data instanceof AdminSettingsCurrencyCreateInput
            || ($data instanceof AdminSettingsCurrency && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.currencies.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        // Update
        if ($data instanceof AdminSettingsCurrencyUpdateInput
            || ($data instanceof AdminSettingsCurrency && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.currencies.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        // REST Delete
        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.currencies.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    // ─── Create ──────────────────────────────────────────────────────────────

    protected function handleCreate(array $input): AdminSettingsCurrency
    {
        $this->validateCreatePayload($input);

        $currency = $this->currencyRepository->create($this->filterRepositoryPayload($input));

        // CurrencyRepository::create returns a contract — re-fetch as Eloquent model.
        $fresh = Currency::find($currency->id);

        return $this->itemProvider->mapToDtoPublic($fresh);
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    protected function handleUpdate(int $id, array $input): AdminSettingsCurrency
    {
        $currency = Currency::find($id);
        if (! $currency) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.currency.not-found'));
        }

        $this->validateUpdatePayload($input);

        // Code is immutable on update (parity with monolith) — drop it from payload.
        $payload = $this->filterRepositoryPayload($input);
        unset($payload['code']);

        $this->currencyRepository->update($payload, $id);

        return $this->itemProvider->mapToDtoPublic(Currency::find($id));
    }

    // ─── Delete ──────────────────────────────────────────────────────────────

    protected function handleDelete(int $id): array
    {
        $currency = Currency::find($id);
        if (! $currency) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.currency.not-found'));
        }

        // Guard 1: cannot delete the last remaining currency.
        if (Currency::count() <= 1) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.currency.cannot-delete-last'),
                400,
            );
        }

        // Guard 2: cannot delete a currency used as any channel's base currency.
        if (DB::table('channels')->where('base_currency_id', $id)->exists()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.currency.cannot-delete-channel-base'),
                400,
            );
        }

        try {
            $this->currencyRepository->delete($id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.currency.delete-failed'),
                500,
            );
        }

        return ['message' => __('bagistoapi::app.admin.settings.currency.deleted')];
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'code' => ['required', 'string', 'size:3', 'alpha'],
            'name' => ['required', 'string'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        // Unique code (case-insensitive — model uppercases on save).
        $code = strtoupper((string) $input['code']);
        if (DB::table('currencies')->where('code', $code)->exists()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.currency.code-unique'), 422);
        }
    }

    protected function validateUpdatePayload(array $input): void
    {
        $rules = [
            'name' => ['required', 'string'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }
    }

    // ─── Permissions ─────────────────────────────────────────────────────────

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.currency.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.currency.no-permission'));
        }
    }

    // ─── Input resolution ────────────────────────────────────────────────────

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsCurrencyCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsCurrencyUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsCurrencyUpdateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    /**
     * Map camelCase GraphQL args → snake_case the validator + repository expect.
     */
    protected function dtoToArray(object $dto, array $rawArgs = []): array
    {
        $result = [];

        $camelToSnake = [
            'groupSeparator'   => 'group_separator',
            'decimalSeparator' => 'decimal_separator',
            'currencyPosition' => 'currency_position',
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

        return array_intersect_key($input, array_flip([
            'code',
            'name',
            'symbol',
            'decimal',
            'group_separator',
            'decimal_separator',
            'currency_position',
        ]));
    }
}
