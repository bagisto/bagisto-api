<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingTemplate;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/templates + adminMarketingTemplates.
 *
 * Filters: name (LIKE), status (exact: active|inactive|draft).
 * Sort:    id (default desc), name.
 *
 * Listing rows include content (the body is the whole point of a template,
 * and the rows are small without pivots/relations).
 */
class AdminMarketingTemplateCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('marketing_templates')->select(
            'marketing_templates.id',
            'marketing_templates.name',
            'marketing_templates.status',
            'marketing_templates.content',
            'marketing_templates.created_at',
            'marketing_templates.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('marketing_templates.name', 'like', '%'.$args['name'].'%');
        }

        if (isset($args['status']) && $args['status'] !== '') {
            $query->where('marketing_templates.status', (string) $args['status']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'   => 'marketing_templates.id',
            'name' => 'marketing_templates.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'marketing_templates.id', $direction);
    }

    protected function mapRow(object $row): AdminMarketingTemplate
    {
        $dto = new AdminMarketingTemplate;

        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->status = $row->status;
        $dto->content = $row->content;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
