<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsRole;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\User\Models\Role;

class AdminSettingsRoleItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.role.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Role::find($id);
    }

    protected function mapToDto(object $role): AdminSettingsRole
    {
        /** @var Role $role */
        $dto = new AdminSettingsRole;

        $perms = $role->permissions;
        if (is_string($perms)) {
            $decoded = json_decode($perms, true);
            $perms = is_array($decoded) ? $decoded : null;
        }

        $dto->id = (int) $role->id;
        $dto->name = $role->name;
        $dto->description = $role->description;
        $dto->permissionType = $role->permission_type;
        $dto->permissions = is_array($perms) ? $perms : null;
        $dto->createdAt = $role->created_at?->toIso8601String();
        $dto->updatedAt = $role->updated_at?->toIso8601String();

        return $dto;
    }

    public function mapToDtoPublic(object $role): AdminSettingsRole
    {
        return $this->mapToDto($role);
    }
}
