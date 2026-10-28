<?php

namespace Webkul\BagistoApi\Admin\State;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminAttributeFamily;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

class AdminAttributeFamilyCollectionProvider extends AbstractAdminCollectionProvider
{
    /**
     * Get sortable.
     */
    protected function getSortable(): array
    {
        return ['id', 'code', 'name'];
    }

    /**
     * Build query.
     */
    protected function buildQuery(array $args)
    {
        return DB::table('attribute_families')
            ->select(
                'id',
                'code',
                'name',
            );
    }

    /**
     * Apply filters.
     */
    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['id'])) {
            $ids = is_array($args['id'])
                ? $args['id']
                : array_filter(array_map('trim', explode(',', (string) $args['id'])));
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if ($ids) {
                $query->whereIn('id', $ids);
            }
        }

        if (! empty($args['code'])) {
            $query->where('code', db_grammar()->caseInsensitiveLike(), '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('name', db_grammar()->caseInsensitiveLike(), '%'.$args['name'].'%');
        }
    }

    /**
     * Apply sort.
     */
    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $query->orderBy($column, $direction);
    }

    /**
     * Map row.
     */
    protected function mapRow(object $row): AdminAttributeFamily
    {
        $dto = new AdminAttributeFamily;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->name;

        $dto->attributeGroups = null;

        return $dto;
    }
}
