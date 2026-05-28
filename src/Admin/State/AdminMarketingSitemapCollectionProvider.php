<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSitemap;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/sitemaps + adminMarketingSitemaps.
 *
 * Filters: file_name (LIKE).
 * Sort:    id (default desc), file_name.
 *
 * Listing rows omit detail-only fields (indexFile / generatedSitemaps).
 */
class AdminMarketingSitemapCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'file_name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('sitemaps')->select(
            'sitemaps.id',
            'sitemaps.file_name',
            'sitemaps.path',
            'sitemaps.generated_at',
            'sitemaps.created_at',
            'sitemaps.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['file_name'])) {
            $query->where('sitemaps.file_name', 'like', '%'.$args['file_name'].'%');
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'        => 'sitemaps.id',
            'file_name' => 'sitemaps.file_name',
        ];

        $query->orderBy($columnMap[$column] ?? 'sitemaps.id', $direction);
    }

    protected function mapRow(object $row): AdminMarketingSitemap
    {
        $dto = new AdminMarketingSitemap;

        $dto->id = (int) $row->id;
        $dto->fileName = $row->file_name;
        $dto->path = $row->path;
        $dto->generatedAt = $row->generated_at ? Carbon::parse($row->generated_at)->toIso8601String() : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
