<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminCmsPage;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for the admin CMS → Pages datagrid endpoint.
 *
 * REST: GET /api/admin/cms/pages
 *
 * Mirrors Webkul\Admin\DataGrids\CMS\CMSPageDataGrid — same join
 * (cms_pages × cms_page_translations on the active locale × cms_page_channels × channels),
 * same filters, same sort columns.
 */
class AdminCmsPageCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'page_title', 'url_key', 'created_at'];
    }

    protected function buildQuery(array $args)
    {
        $locale = $args['locale'] ?? app()->getLocale();

        return DB::table('cms_pages')
            ->leftJoin('cms_page_translations as cpt', function ($j) use ($locale) {
                $j->on('cms_pages.id', '=', 'cpt.cms_page_id')
                    ->where('cpt.locale', $locale);
            })
            ->leftJoin('cms_page_channels', 'cms_pages.id', '=', 'cms_page_channels.cms_page_id')
            ->leftJoin('channels', 'cms_page_channels.channel_id', '=', 'channels.id')
            ->select(
                'cms_pages.id',
                'cms_pages.created_at',
                'cms_pages.updated_at',
                'cpt.page_title',
                'cpt.url_key',
                'cpt.locale as cpt_locale',
                DB::raw('GROUP_CONCAT(DISTINCT channels.code) as channel'),
            )
            ->groupBy('cms_pages.id', 'cpt.locale', 'cms_pages.created_at', 'cms_pages.updated_at', 'cpt.page_title', 'cpt.url_key');
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['id'])) {
            $query->where('cms_pages.id', (int) $args['id']);
        }

        if (! empty($args['page_title'])) {
            $query->where('cpt.page_title', 'like', '%'.$args['page_title'].'%');
        }

        if (! empty($args['url_key'])) {
            $query->where('cpt.url_key', 'like', '%'.$args['url_key'].'%');
        }

        if (! empty($args['channel'])) {
            $query->where('cms_page_channels.channel_id', (int) $args['channel']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'         => 'cms_pages.id',
            'page_title' => 'cpt.page_title',
            'url_key'    => 'cpt.url_key',
            'created_at' => 'cms_pages.created_at',
        ];

        $orderColumn = $columnMap[$column] ?? 'cms_pages.id';

        $query->orderBy($orderColumn, $direction);
    }

    protected function mapRow(object $row): AdminCmsPage
    {
        $dto = new AdminCmsPage;

        $dto->id = (int) $row->id;
        $dto->urlKey = $row->url_key;
        $dto->pageTitle = $row->page_title;
        $dto->channel = $row->channel;
        $dto->locale = $row->cpt_locale ?? app()->getLocale();
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
