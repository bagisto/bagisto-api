<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;
use Webkul\BagistoApi\Support\Sql;

class AdminCmsPageExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    /**
     * Export permission.
     */
    protected function exportPermission(): string
    {
        return 'cms.edit';
    }

    /**
     * Export filename.
     */
    protected function exportFilename(): string
    {
        return 'cms-pages';
    }

    /**
     * Export headers.
     */
    protected function exportHeaders(): array
    {
        return ['ID', 'Page Title', 'URL Key', 'Channel', 'Locale'];
    }

    /**
     * Export row.
     */
    protected function exportRow(object $row): array
    {
        return [
            $row->id,
            (string) ($row->page_title ?? ''),
            (string) ($row->url_key ?? ''),
            (string) ($row->channel ?? ''),
            (string) ($row->cpt_locale ?? ''),
        ];
    }

    /**
     * Export query.
     */
    protected function exportQuery(array $args)
    {
        $locale = $args['locale'] ?? app()->getLocale();

        $query = DB::table('cms_pages')
            ->leftJoin('cms_page_translations as cpt', function ($index) use ($locale) {
                $index->on('cms_pages.id', '=', 'cpt.cms_page_id')
                    ->where('cpt.locale', $locale);
            })
            ->leftJoin('cms_page_channels', 'cms_pages.id', '=', 'cms_page_channels.cms_page_id')
            ->leftJoin('channels', 'cms_page_channels.channel_id', '=', 'channels.id')
            ->select(
                'cms_pages.id',
                'cpt.page_title',
                'cpt.url_key',
                'cpt.locale as cpt_locale',
                DB::raw(Sql::distinctList('channels.code').' as channel'),
            )
            ->groupBy('cms_pages.id', 'cpt.page_title', 'cpt.url_key', 'cpt.locale')
            ->orderByDesc('cms_pages.id');

        if (! empty($args['id'])) {
            $query->where('cms_pages.id', (int) $args['id']);
        }

        if (! empty($args['page_title'])) {
            $query->where('cpt.page_title', db_grammar()->caseInsensitiveLike(), '%'.$args['page_title'].'%');
        }

        if (! empty($args['url_key'])) {
            $query->where('cpt.url_key', db_grammar()->caseInsensitiveLike(), '%'.$args['url_key'].'%');
        }

        if (! empty($args['channel'])) {
            $query->where('cms_page_channels.channel_id', (int) $args['channel']);
        }

        return $query;
    }
}
