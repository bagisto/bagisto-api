<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminRmaRule;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminRmaRuleCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->authorizedAdmin('sales.rma.rules');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('rma_rules')->select('id', 'name', 'description', 'status', 'return_period', 'default', 'created_at', 'updated_at');
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('name', 'like', '%'.$args['name'].'%');
        }

        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null) {
            $query->where('status', (int) $args['status']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $query->orderBy(in_array($column, ['id', 'name'], true) ? $column : 'id', $direction);
    }

    protected function mapRow(object $row): AdminRmaRule
    {
        return AdminRmaRuleItemProvider::toDto($row);
    }
}
