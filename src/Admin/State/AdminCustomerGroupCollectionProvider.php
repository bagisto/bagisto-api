<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGroup;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * GET /api/admin/customers/groups + adminCustomerGroups GraphQL.
 *
 * Filters: code (LIKE), name (LIKE), is_user_defined.
 * Sort: id (default desc), code, name.
 */
class AdminCustomerGroupCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'code', 'name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('customer_groups')
            ->select(
                'customer_groups.id',
                'customer_groups.code',
                'customer_groups.name',
                'customer_groups.is_user_defined',
                'customer_groups.created_at',
                'customer_groups.updated_at',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('customer_groups.code', 'like', '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('customer_groups.name', 'like', '%'.$args['name'].'%');
        }

        if (isset($args['is_user_defined']) && $args['is_user_defined'] !== '' && $args['is_user_defined'] !== null) {
            $query->where('customer_groups.is_user_defined', (int) $args['is_user_defined']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = [
            'id'   => 'customer_groups.id',
            'code' => 'customer_groups.code',
            'name' => 'customer_groups.name',
        ];

        $query->orderBy($map[$column] ?? 'customer_groups.id', $direction);
    }

    protected function mapRow(object $row): AdminCustomerGroup
    {
        $dto = new AdminCustomerGroup;
        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->name;
        $dto->isUserDefined = $row->is_user_defined !== null ? (int) $row->is_user_defined : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
