<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingUrlRewrite;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/url-rewrites + adminMarketingUrlRewrites.
 *
 * Filters: entity_type (exact), request_path (LIKE), redirect_type (exact), locale (exact).
 * Sort:    id (default desc), entity_type, locale, redirect_type.
 */
class AdminMarketingUrlRewriteCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'entity_type', 'locale', 'redirect_type'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('url_rewrites')->select(
            'url_rewrites.id',
            'url_rewrites.entity_type',
            'url_rewrites.request_path',
            'url_rewrites.target_path',
            'url_rewrites.redirect_type',
            'url_rewrites.locale',
            'url_rewrites.created_at',
            'url_rewrites.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['entity_type'])) {
            $query->where('url_rewrites.entity_type', (string) $args['entity_type']);
        }

        if (! empty($args['request_path'])) {
            $query->where('url_rewrites.request_path', 'like', '%'.$args['request_path'].'%');
        }

        if (! empty($args['redirect_type'])) {
            $query->where('url_rewrites.redirect_type', (string) $args['redirect_type']);
        }

        if (! empty($args['locale'])) {
            $query->where('url_rewrites.locale', (string) $args['locale']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'            => 'url_rewrites.id',
            'entity_type'   => 'url_rewrites.entity_type',
            'locale'        => 'url_rewrites.locale',
            'redirect_type' => 'url_rewrites.redirect_type',
        ];

        $query->orderBy($columnMap[$column] ?? 'url_rewrites.id', $direction);
    }

    protected function mapRow(object $row): AdminMarketingUrlRewrite
    {
        $dto = new AdminMarketingUrlRewrite;

        $dto->id = (int) $row->id;
        $dto->entityType = $row->entity_type;
        $dto->requestPath = $row->request_path;
        $dto->targetPath = $row->target_path;
        $dto->redirectType = $row->redirect_type;
        $dto->locale = $row->locale;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
