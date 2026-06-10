<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageListDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCmsPage;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsCmsPagePreviewUrl;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Provider for the admin CMS → Pages datagrid endpoint.
 *
 * REST: GET /api/admin/cms/pages    — maps each row to AdminCmsPageListDto.
 * GraphQL: adminCmsPages            — hydrates AdminCmsPage Eloquent models so
 *                                      translations and channels are field-selectable.
 *
 * Mirrors Webkul\Admin\DataGrids\CMS\CMSPageDataGrid — same join
 * (cms_pages × cms_page_translations on the active locale × cms_page_channels × channels),
 * same filters, same sort columns.
 */
class AdminCmsPageCollectionProvider extends AbstractAdminCollectionProvider
{
    use BuildsCmsPagePreviewUrl;

    protected bool $graphql = false;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->graphql = ! empty($context['graphql_operation_name']);

        $args = $context['args'] ?? array_merge(
            request()->query(),
            request()->input('filter') ?? []
        );

        [$page, $perPage] = $this->resolvePaging($args);

        $query = $this->buildQuery($args);
        $this->applyFilters($query, $args);
        $this->applySort($query, $args);

        $total = $this->countTotal($query);

        $rows = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();
        $items = $this->mapRows($rows);

        return new Paginator(new LengthAwarePaginator(
            $items, $total, $perPage, $page, ['path' => request()->url()]
        ));
    }

    protected function mapRows($rows): array
    {
        if (! $this->graphql) {
            return $rows->map(fn ($row) => $this->mapRow($row))->all();
        }

        $ids = $rows->pluck('id')->map(fn ($v) => (int) $v)->all();

        if (empty($ids)) {
            return [];
        }

        $pages = AdminCmsPage::with(['translations', 'channels'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn ($id) => $pages->get($id))
            ->filter()
            ->values()
            ->all();
    }

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
                'cms_pages.layout',
                'cms_pages.created_at',
                'cms_pages.updated_at',
                'cpt.page_title',
                'cpt.url_key',
                'cpt.meta_title',
                'cpt.meta_keywords',
                'cpt.meta_description',
                'cpt.locale as cpt_locale',
                DB::raw('GROUP_CONCAT(DISTINCT '.DB::getTablePrefix().'channels.code) as channel'),
            )
            ->groupBy(
                'cms_pages.id',
                'cms_pages.layout',
                'cpt.locale',
                'cms_pages.created_at',
                'cms_pages.updated_at',
                'cpt.page_title',
                'cpt.url_key',
                'cpt.meta_title',
                'cpt.meta_keywords',
                'cpt.meta_description',
            );
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

    protected function mapRow(object $row): AdminCmsPageListDto
    {
        $dto = new AdminCmsPageListDto;

        $dto->id = (int) $row->id;
        $dto->urlKey = $row->url_key;
        $dto->pageTitle = $row->page_title;
        $dto->metaTitle = $row->meta_title;
        $dto->metaKeywords = $row->meta_keywords;
        $dto->metaDescription = $row->meta_description;
        $dto->layout = $row->layout;
        $dto->channel = $row->channel;
        $dto->locale = $row->cpt_locale ?? app()->getLocale();
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;
        $dto->previewUrl = $this->buildPreviewUrl($row->url_key);

        $codes = $row->channel ? array_values(array_filter(array_map('trim', explode(',', (string) $row->channel)))) : [];
        $dto->channels = $codes;

        return $dto;
    }
}
