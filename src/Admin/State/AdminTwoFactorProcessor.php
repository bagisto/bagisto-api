<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Webkul\Admin\Mail\Admin\BackupCodesNotification;
use Webkul\BagistoApi\Admin\Dto\AdminTwoFactorCodeInput;
use Webkul\BagistoApi\Admin\Models\AdminTwoFactor;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\User\Models\Admin;
use Webkul\User\Repositories\AdminRepository;

/**
 * Two-factor enable / disable, mirroring TwoFactorController::enable and ::disable. Core's
 * "passed verification this session" check on disable becomes a required code here.
 */
class AdminTwoFactorProcessor implements ProcessorInterface
{
    public function __construct(protected AdminRepository $adminRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = $this->adminRepository->find(AdminReadPermissions::authorize()->id);

        $code = $this->code($data, $context);

        $disable = $operation instanceof Mutation
            ? $operation->getName() === 'disable'
            : str_ends_with((string) $operation->getUriTemplate(), '/disable');

        $result = $disable ? $this->disable($admin, $code) : $this->enable($admin, $code);

        if ($operation instanceof Mutation) {
            return $result;
        }

        return new JsonResponse([
            'enabled' => $result->enabled,
            'backupCodes' => $result->backup_codes,
            'message' => $result->message,
        ], 200);
    }

    protected function enable(Admin $admin, ?string $code): AdminTwoFactor
    {
        $validator = Validator::make(['code' => $code], ['code' => 'required|digits:6']);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        if (! $admin->two_factor_secret) {
            throw new InvalidInputException(__('bagistoapi::app.admin.account.two-factor.setup-required'), 422);
        }

        if (! two_factor_authentication()->verifyQrCode(decrypt($admin->two_factor_secret), $code)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.account.two-factor.invalid-code'), 422);
        }

        $backupCodes = two_factor_authentication()->generateBackupCodes();

        $this->adminRepository->update([
            'two_factor_enabled' => true,
            'two_factor_verified_at' => now(),
            'two_factor_backup_codes' => two_factor_authentication()->hashBackupCodes($backupCodes),
        ], $admin->id);

        try {
            Mail::to($admin->email)->send(new BackupCodesNotification($admin, $backupCodes));
        } catch (\Exception $e) {
            report($e);
        }

        return $this->result($admin, true, __('bagistoapi::app.admin.account.two-factor.enabled-success'), $backupCodes);
    }

    protected function disable(Admin $admin, ?string $code): AdminTwoFactor
    {
        if ($admin->two_factor_enabled) {
            $this->assertSecondFactor($admin, (string) $code);
        }

        $this->adminRepository->update(two_factor_authentication()->getDisableValues(), $admin->id);

        return $this->result($admin, false, __('bagistoapi::app.admin.account.two-factor.disabled-success'));
    }

    /**
     * Accept a current authenticator code or an unused backup code, consuming the backup code.
     */
    protected function assertSecondFactor(Admin $admin, string $code): void
    {
        if ($code !== '' && $admin->two_factor_secret
            && two_factor_authentication()->verifyQrCode(decrypt($admin->two_factor_secret), $code)) {
            return;
        }

        if ($code !== '') {
            $remaining = two_factor_authentication()->verifyBackupCode($admin->two_factor_backup_codes ?? [], $code);

            if ($remaining !== null) {
                $this->adminRepository->update(['two_factor_backup_codes' => $remaining], $admin->id);

                return;
            }
        }

        throw new InvalidInputException(__('bagistoapi::app.admin.account.two-factor.invalid-code'), 422);
    }

    protected function result(Admin $admin, bool $enabled, string $message, ?array $backupCodes = null): AdminTwoFactor
    {
        $result = new AdminTwoFactor;
        $result->id = (int) $admin->id;
        $result->enabled = $enabled;
        $result->backup_codes = $backupCodes;
        $result->message = $message;

        return $result;
    }

    protected function code(mixed $data, array $context): ?string
    {
        $code = $data instanceof AdminTwoFactorCodeInput ? $data->code : null;

        $code ??= $context['args']['input']['code'] ?? request()->input('code');

        return $code === null ? null : (string) $code;
    }
}
