<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsRole;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/roles + adminSettingsRoles.
 */
class AdminSettingsRoleCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('roles')->select(
            'roles.id',
            'roles.name',
            'roles.description',
            'roles.permission_type',
            'roles.permissions',
            'roles.created_at',
            'roles.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('roles.name', 'like', '%'.$args['name'].'%');
        }

        if (! empty($args['permission_type'])) {
            $query->where('roles.permission_type', $args['permission_type']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'   => 'roles.id',
            'name' => 'roles.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'roles.id', $direction);
    }

    protected function mapRow(object $row): AdminSettingsRole
    {
        $dto = new AdminSettingsRole;

        $perms = $row->permissions;
        if (is_string($perms)) {
            $decoded = json_decode($perms, true);
            $perms = is_array($decoded) ? $decoded : null;
        }

        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->description = $row->description;
        $dto->permissionType = $row->permission_type;
        $dto->permissions = $perms;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
