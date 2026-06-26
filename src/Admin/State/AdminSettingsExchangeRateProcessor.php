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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsExchangeRateCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsExchangeRateUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsExchangeRate;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\CurrencyExchangeRate;
use Webkul\Core\Repositories\ExchangeRateRepository;

/**
 * Handles POST / PUT / DELETE for the AdminSettingsExchangeRate resource.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\ExchangeRateController.
 *
 * Permission resolution mirrors AdminCategoryProcessor — read
 * role->permission_type / role->permissions directly, never call bouncer()
 * (Sanctum-token requests have no session-bound admin).
 */
class AdminSettingsExchangeRateProcessor implements ProcessorInterface
{
    public function __construct(
        protected ExchangeRateRepository $exchangeRateRepository,
        protected AdminSettingsExchangeRateItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $operation->getName() === 'delete' && $data instanceof AdminSettingsExchangeRateUpdateInput) {
            $this->assertPermission($admin, 'settings.exchange_rates.delete');
            $id = (int) basename((string) $this->resolveUpdateId($data, $context));

            return $this->handleDelete($id, true);
        }

        if ($data instanceof AdminSettingsExchangeRateCreateInput
            || ($data instanceof AdminSettingsExchangeRate && $operation instanceof Post)) {
            $this->assertPermission($admin, 'settings.exchange_rates.create');

            return $this->handleCreate($this->resolveCreateInput($data, $context, $isGraphQL));
        }

        if ($data instanceof AdminSettingsExchangeRateUpdateInput
            || ($data instanceof AdminSettingsExchangeRate && $operation instanceof Put)) {
            $this->assertPermission($admin, 'settings.exchange_rates.edit');
            $id = (int) ($uriVariables['id'] ?? basename((string) $this->resolveUpdateId($data, $context)));

            return $this->handleUpdate($id, $this->resolveUpdateInput($data, $context, $isGraphQL));
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.exchange_rates.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleCreate(array $input): AdminSettingsExchangeRate
    {
        $this->validateCreatePayload($input);

        Event::dispatch('core.exchange_rate.create.before');

        $rate = $this->exchangeRateRepository->create([
            'target_currency' => (int) $input['target_currency'],
            'rate'            => (float) $input['rate'],
        ]);

        Event::dispatch('core.exchange_rate.create.after', $rate);

        return $this->fetchAndMap((int) $rate->id);
    }

    protected function handleUpdate(int $id, array $input): AdminSettingsExchangeRate
    {
        $existing = CurrencyExchangeRate::find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.exchange-rate.not-found'));
        }

        $payload = [
            'target_currency' => isset($input['target_currency']) && $input['target_currency'] !== '' && $input['target_currency'] !== null
                ? (int) $input['target_currency']
                : (int) $existing->target_currency,
            'rate' => isset($input['rate']) && $input['rate'] !== '' && $input['rate'] !== null
                ? (float) $input['rate']
                : (float) $existing->rate,
        ];

        $this->validateUpdatePayload($payload, $id);

        Event::dispatch('core.exchange_rate.update.before', $id);

        $rate = $this->exchangeRateRepository->update($payload, $id);

        Event::dispatch('core.exchange_rate.update.after', $rate);

        return $this->fetchAndMap($id);
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminSettingsExchangeRate
    {
        $existing = CurrencyExchangeRate::with('currency')->find($id);
        if (! $existing) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.exchange-rate.not-found'));
        }

        try {
            Event::dispatch('core.exchange_rate.delete.before', $id);

            $this->exchangeRateRepository->delete($id);

            Event::dispatch('core.exchange_rate.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.exchange-rate.delete-failed'),
                500,
            );
        }

        if ($asResource) {
            $snapshot = $this->mapModel($existing);
            $snapshot->message = __('bagistoapi::app.admin.settings.exchange-rate.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.settings.exchange-rate.deleted')];
    }

    protected function validateCreatePayload(array $input): void
    {
        $rules = [
            'target_currency' => ['required', 'integer', 'exists:currencies,id'],
            'rate'            => ['required', 'numeric', 'gt:0'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        if ($this->targetCurrencyTaken((int) $input['target_currency'], null)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.exchange-rate.duplicate-pair'), 422);
        }
    }

    protected function validateUpdatePayload(array $input, int $excludeId): void
    {
        $rules = [
            'target_currency' => ['required', 'integer', 'exists:currencies,id'],
            'rate'            => ['required', 'numeric', 'gt:0'],
        ];

        $v = Validator::make($input, $rules);
        if ($v->fails()) {
            throw new InvalidInputException($v->errors()->first(), 422);
        }

        if ($this->targetCurrencyTaken((int) $input['target_currency'], $excludeId)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.exchange-rate.duplicate-pair'), 422);
        }
    }

    protected function targetCurrencyTaken(int $currencyId, ?int $excludeId): bool
    {
        $q = DB::table('currency_exchange_rates')->where('target_currency', $currencyId);
        if ($excludeId !== null) {
            $q->where('id', '<>', $excludeId);
        }

        return $q->limit(1)->exists();
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.exchange-rate.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.exchange-rate.no-permission'));
        }
    }

    protected function resolveCreateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsExchangeRateCreateInput) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            unset($rawArgs['id'], $rawArgs['clientMutationId']);

            return $this->dtoToArray($data, $rawArgs);
        }

        return request()->all();
    }

    protected function resolveUpdateId(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminSettingsExchangeRateUpdateInput && $data->id) {
            return $data->id;
        }

        return (string) ($context['args']['input']['id'] ?? $context['args']['id'] ?? '');
    }

    protected function resolveUpdateInput(mixed $data, array $context, bool $isGraphQL = false): array
    {
        if ($isGraphQL && $data instanceof AdminSettingsExchangeRateUpdateInput) {
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
            'targetCurrency' => 'target_currency',
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

    protected function fetchAndMap(int $id): AdminSettingsExchangeRate
    {
        return $this->mapModel(CurrencyExchangeRate::with('currency')->find($id));
    }

    protected function mapModel(object $model): AdminSettingsExchangeRate
    {
        $reflection = new \ReflectionClass($this->itemProvider);
        $method = $reflection->getMethod('mapToDto');
        $method->setAccessible(true);

        return $method->invoke($this->itemProvider, $model);
    }
}
