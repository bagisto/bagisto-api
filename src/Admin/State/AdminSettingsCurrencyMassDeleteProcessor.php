<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsCurrencyMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsCurrencyMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Core\Models\Currency;
use Webkul\Core\Repositories\CurrencyRepository;

/**
 * POST /api/admin/settings/currencies/mass-delete + createAdminSettingsCurrencyMassDelete.
 *
 * Pre-validates the whole batch — if any id is a channel base_currency_id, OR
 * the batch would empty the currencies table, the WHOLE batch is rejected with
 * HTTP 400. Non-existent IDs are silently skipped.
 */
class AdminSettingsCurrencyMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(protected CurrencyRepository $currencyRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'settings.currencies.delete');

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.currency.mass-delete-indices-required'),
                422,
            );
        }

        $indices = array_values(array_unique(array_map('intval', $indices)));

        $existing = Currency::whereIn('id', $indices)->pluck('id')->map(fn ($v) => (int) $v)->all();

        if (empty($existing)) {
            return $this->buildResult([]);
        }

        $totalCurrencies = Currency::count();
        if (count($existing) >= $totalCurrencies) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.currency.cannot-delete-last'),
                400,
            );
        }

        $baseIds = DB::table('channels')
            ->whereIn('base_currency_id', $existing)
            ->pluck('base_currency_id')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->all();

        if (! empty($baseIds)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.currency.cannot-delete-channel-base'),
                400,
            );
        }

        $deleted = [];
        foreach ($existing as $id) {
            try {
                $this->currencyRepository->delete($id);
                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.settings.currency.delete-failed'),
                    500,
                );
            }
        }

        return $this->buildResult($deleted);
    }

    protected function buildResult(array $deleted): AdminSettingsCurrencyMassDelete
    {
        $result = new AdminSettingsCurrencyMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.settings.currency.mass-delete-success');

        return $result;
    }

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

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminSettingsCurrencyMassDeleteInput && ! empty($data->indices)) {
            return $data->indices;
        }

        $fromArgs = $context['args']['input']['indices']
            ?? $context['args']['indices']
            ?? null;

        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        $fromBody = request()->input('indices');
        if (is_array($fromBody)) {
            return $fromBody;
        }

        return [];
    }
}
