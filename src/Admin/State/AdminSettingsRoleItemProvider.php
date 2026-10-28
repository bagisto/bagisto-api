<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsRole;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\User\Models\Role;

class AdminSettingsRoleItemProvider extends AbstractAdminItemProvider
{
    /**
     * Map to dto public.
     */
    public function mapToDtoPublic(object $role): AdminSettingsRole
    {
        return $this->mapToDto($role);
    }

    /**
     * Get not found lang key.
     */
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.role.not-found';
    }

    /**
     * Find entity.
     */
    protected function findEntity(int $id): ?object
    {
        return Role::find($id);
    }

    /**
     * Map to dto.
     */
    protected function mapToDto(object $role): AdminSettingsRole
    {
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
}
