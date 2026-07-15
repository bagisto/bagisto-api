<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminPermissions;
use Webkul\BagistoApi\Exception\AuthenticationException;

class AdminPermissionsProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return [self::buildPayload($admin)];
    }

    /**
     * @return array<string, mixed>
     */
    public static function buildPayload(object $admin): array
    {
        $role = $admin->role ?? null;
        $type = $role->permission_type ?? 'custom';

        if ($type === 'all') {
            $permissions = ['*'];
        } else {
            $perms = $role->permissions ?? [];

            if (is_string($perms)) {
                $perms = array_filter(array_map('trim', explode(',', $perms)));
            }

            $permissions = array_values((array) $perms);
        }

        return [
            'id' => 'permissions',
            'permissionType' => $type,
            'permissions' => $permissions,
        ];
    }

    public static function toDto(array $payload): AdminPermissions
    {
        $dto = new AdminPermissions;
        $dto->id = $payload['id'] ?? 'permissions';
        $dto->permission_type = $payload['permissionType'] ?? null;
        $dto->permissions = $payload['permissions'] ?? [];

        return $dto;
    }
}
