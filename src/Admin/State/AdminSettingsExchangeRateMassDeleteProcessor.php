<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsExchangeRateMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsExchangeRateMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Core\Models\CurrencyExchangeRate;
use Webkul\Core\Repositories\ExchangeRateRepository;

/**
 * POST /api/admin/settings/exchange-rates/mass-delete +
 * createAdminSettingsExchangeRateMassDelete.
 *
 * Deletes each provided ID, firing the before/after events. Non-existent IDs
 * are silently skipped.
 */
class AdminSettingsExchangeRateMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        protected ExchangeRateRepository $exchangeRateRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'settings.exchange_rates.delete');

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.exchange-rate.mass-delete-indices-required'), 422);
        }

        $deleted = [];

        foreach ($indices as $index) {
            $id = (int) $index;
            $row = CurrencyExchangeRate::find($id);

            if (! $row) {
                continue;
            }

            try {
                Event::dispatch('core.exchange_rate.delete.before', $id);

                $this->exchangeRateRepository->delete($id);

                Event::dispatch('core.exchange_rate.delete.after', $id);

                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.settings.exchange-rate.delete-failed'),
                    500,
                );
            }
        }

        $result = new AdminSettingsExchangeRateMassDelete;
        $result->id = 1; // placeholder — serialiser needs a non-null id for IRI generation
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.settings.exchange-rate.mass-delete-success');

        return $result;
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

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminSettingsExchangeRateMassDeleteInput && ! empty($data->indices)) {
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
