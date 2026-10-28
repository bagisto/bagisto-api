<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Auth\AdminApiCredential;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\User\Models\Admin;

class AdminProfileProvider implements ProviderInterface
{
    /**
     * Provide the signed-in admin's profile.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return [self::toArray($admin, AdminAuthHelper::credential())];
    }

    /**
     * Map an Admin model, and the token the request was made with, to the AdminProfile payload shape.
     */
    public static function toArray(Admin $admin, ?AdminApiCredential $credential = null): array
    {
        return [
            'id' => (string) $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'image' => $admin->image,
            'status' => (string) $admin->status,
            'roleId' => $admin->role_id,
            'roleName' => $admin->role?->name,
            'tokenKind' => $credential?->kind(),
            'tokenId' => $credential?->id(),
            'tokenExpiresAt' => $credential?->expiresAt?->toIso8601String(),
            'success' => true,
            'message' => null,
        ];
    }
}
