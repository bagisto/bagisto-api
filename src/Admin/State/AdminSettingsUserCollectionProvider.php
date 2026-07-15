<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminSettingsUser;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/users + adminSettingsUsers GraphQL query.
 */
class AdminSettingsUserCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'name', 'email'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('admins')
            ->leftJoin('roles', 'admins.role_id', '=', 'roles.id')
            ->select(
                'admins.id',
                'admins.name',
                'admins.email',
                'admins.role_id',
                'roles.name as role_name',
                'admins.status',
                'admins.image',
                'admins.created_at',
                'admins.updated_at',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('admins.name', 'like', '%'.$args['name'].'%');
        }

        if (! empty($args['email'])) {
            $query->where('admins.email', 'like', '%'.$args['email'].'%');
        }

        if (isset($args['role_id']) && $args['role_id'] !== '' && $args['role_id'] !== null) {
            $query->where('admins.role_id', (int) $args['role_id']);
        }

        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null) {
            $query->where('admins.status', (int) $args['status']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id' => 'admins.id',
            'name' => 'admins.name',
            'email' => 'admins.email',
        ];

        $query->orderBy($columnMap[$column] ?? 'admins.id', $direction);
    }

    protected function mapRow(object $row): AdminSettingsUser
    {
        $dto = new AdminSettingsUser;

        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->email = $row->email;
        $dto->roleId = $row->role_id !== null ? (int) $row->role_id : null;
        $dto->roleName = $row->role_name ?? null;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->image = $row->image ?? null;
        $dto->imageUrl = ! empty($row->image) ? Storage::url($row->image) : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
