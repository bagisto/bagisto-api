<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsUser;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\User\Models\Admin;

class AdminSettingsUserItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.user.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Admin::with('role')->find($id);
    }

    protected function mapToDto(object $admin): AdminSettingsUser
    {
        /** @var Admin $admin */
        $dto = new AdminSettingsUser;

        $dto->id = (int) $admin->id;
        $dto->name = $admin->name;
        $dto->email = $admin->email;
        $dto->roleId = $admin->role_id !== null ? (int) $admin->role_id : null;
        $dto->roleName = $admin->role?->name;
        $dto->status = $admin->status !== null ? (int) $admin->status : null;
        $dto->image = $admin->image ?? null;
        $dto->imageUrl = $admin->image_url ?? null;
        $dto->createdAt = $admin->created_at?->toIso8601String();
        $dto->updatedAt = $admin->updated_at?->toIso8601String();

        return $dto;
    }
}
