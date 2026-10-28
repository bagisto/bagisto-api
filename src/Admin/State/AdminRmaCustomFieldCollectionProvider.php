<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminRmaCustomField;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminRmaCustomFieldCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->authorizedAdmin('sales.rma.custom-fields');

        return parent::provide($operation, $uriVariables, $context);
    }

    /**
     * Get sortable.
     */
    protected function getSortable(): array
    {
        return ['id', 'position', 'code'];
    }

    /**
     * Build query.
     */
    protected function buildQuery(array $args)
    {
        return DB::table('rma_custom_fields')->select('id', 'code', 'label', 'type', 'is_required', 'position', 'input_validation', 'status', 'created_at', 'updated_at');
    }

    /**
     * Apply filters.
     */
    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('code', db_grammar()->caseInsensitiveLike(), '%'.$args['code'].'%');
        }

        if (! empty($args['label'])) {
            $query->where('label', db_grammar()->caseInsensitiveLike(), '%'.$args['label'].'%');
        }

        if (! empty($args['type'])) {
            $query->where('type', $args['type']);
        }

        if (
            isset($args['status'])
            && $args['status'] !== ''
            && $args['status'] !== null
        ) {
            $query->where('status', (int) $args['status']);
        }
    }

    /**
     * Apply sort.
     */
    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $query->orderBy(in_array($column, ['id', 'position', 'code'], true) ? $column : 'id', $direction);
    }

    /**
     * Map row.
     */
    protected function mapRow(object $row): AdminRmaCustomField
    {
        return AdminRmaCustomFieldItemProvider::toDto($row, true);
    }
}
