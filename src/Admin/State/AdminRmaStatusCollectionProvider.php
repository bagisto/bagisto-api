<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminRmaStatus;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminRmaStatusCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->authorizedAdmin('sales.rma.statuses');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'title'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('rma_statuses')->select('id', 'title', 'status', 'color', 'default', 'created_at', 'updated_at');
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['title'])) {
            $query->where('title', 'like', '%'.$args['title'].'%');
        }

        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null) {
            $query->where('status', (int) $args['status']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $query->orderBy(in_array($column, ['id', 'title'], true) ? $column : 'id', $direction);
    }

    protected function mapRow(object $row): AdminRmaStatus
    {
        return AdminRmaStatusItemProvider::toDto($row);
    }
}
