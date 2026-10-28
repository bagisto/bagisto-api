<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminTwoFactor;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\User\Repositories\AdminRepository;

/**
 * Two-factor setup, mirroring TwoFactorController::setup. A token carries no "verified this
 * session" state, so an admin who already has two-factor on is refused instead of shown the secret.
 */
class AdminTwoFactorProvider implements ProviderInterface
{
    public function __construct(protected AdminRepository $adminRepository) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [$this->setup()];
    }

    public function setup(): AdminTwoFactor
    {
        $admin = $this->adminRepository->find(AdminReadPermissions::authorize()->id);

        if ($admin->two_factor_enabled) {
            throw new InvalidInputException(__('bagistoapi::app.admin.account.two-factor.already-enabled'), 422);
        }

        if (! $admin->two_factor_secret) {
            $secret = two_factor_authentication()->generateSecretKey();

            $this->adminRepository->update(['two_factor_secret' => encrypt($secret)], $admin->id);
        } else {
            $secret = decrypt($admin->two_factor_secret);
        }

        $qrCode = two_factor_authentication()->generateQrCode($admin->email, $secret);

        $result = new AdminTwoFactor;
        $result->id = (int) $admin->id;
        $result->enabled = false;
        $result->qr_code_svg = $qrCode['qrCodeSvg'];
        $result->qr_code_url = $qrCode['qrCodeUrl'];

        return $result;
    }
}
