<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsExchangeRateUpdateRates;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Core\Models\CurrencyExchangeRate;

class AdminSettingsExchangeRateUpdateRatesProcessor implements ProcessorInterface
{
    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'settings.exchange_rates.edit');

        $provider = config('services.exchange_api.default');
        $class = config('services.exchange_api.'.$provider.'.class');

        if (
            ! $class
            || ! class_exists($class)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.exchange-rate.provider-not-configured'), 422);
        }

        try {
            app($class)->updateRates();
        } catch (\Throwable $exception) {
            report($exception);

            $message = trim((string) $exception->getMessage());

            throw new InvalidInputException(
                $message !== '' ? $message : __('bagistoapi::app.admin.settings.exchange-rate.update-rates-failed'),
                422,
            );
        }

        $result = new AdminSettingsExchangeRateUpdateRates;
        $result->id = 1;
        $result->success = true;
        $result->updated = CurrencyExchangeRate::count();
        $result->message = __('bagistoapi::app.admin.settings.exchange-rate.update-rates-success');

        return $result;
    }

    /**
     * Assert permission.
     */
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

        if (
            ! in_array($permission, $perms, true)
            && ! in_array('*', $perms, true)
        ) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.exchange-rate.no-permission'));
        }
    }
}
