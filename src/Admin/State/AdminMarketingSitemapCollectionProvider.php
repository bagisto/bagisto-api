<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSitemap;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\MapsSitemapChannels;

/**
 * Provider for GET /api/admin/marketing/sitemaps + adminMarketingSitemaps.
 */
class AdminMarketingSitemapCollectionProvider extends AbstractAdminCollectionProvider
{
    use MapsSitemapChannels;

    protected function getSortable(): array
    {
        return ['id', 'file_name'];
    }

    protected function buildQuery(array $args)
    {
        $prefix = DB::getTablePrefix();

        return DB::table('sitemaps')
            ->select(
                'sitemaps.id',
                'sitemaps.file_name',
                'sitemaps.path',
                'sitemaps.generated_at',
                'sitemaps.created_at',
                'sitemaps.updated_at',
            )
            ->addSelect(DB::raw('GROUP_CONCAT(DISTINCT CONCAT('.$prefix.'channels.id, "::", '.$prefix.'channels.code, "::", COALESCE('.$prefix.'channels.hostname, "")) SEPARATOR "||") as channel_list'))
            ->leftJoin('sitemap_channels', 'sitemaps.id', '=', 'sitemap_channels.sitemap_id')
            ->leftJoin('channels', 'sitemap_channels.channel_id', '=', 'channels.id')
            ->groupBy(
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

        if (! empty($args['channel_id'])) {
            $channelId = (int) $args['channel_id'];

            $query->whereExists(function ($sub) use ($channelId) {
                $sub->select(DB::raw(1))
                    ->from('sitemap_channels')
                    ->whereColumn('sitemap_channels.sitemap_id', 'sitemaps.id')
                    ->where('sitemap_channels.channel_id', $channelId);
            });
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id' => 'sitemaps.id',
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

        $channels = $this->parseChannelList($row->channel_list ?? null);

        $dto->channels = array_map(fn ($channel) => (int) $channel->id, $channels);
        $dto->urls = $this->sitemapUrls($channels, (int) $row->id, $row->path, $row->file_name);

        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }

    /**
     * Expand the grouped `id::code::hostname` list back into channel objects.
     *
     * @return array<int, object>
     */
    protected function parseChannelList(?string $list): array
    {
        $channels = [];

        foreach (array_filter(explode('||', (string) $list)) as $triple) {
            [$id, $code, $hostname] = array_pad(explode('::', $triple, 3), 3, null);

            if (! $id) {
                continue;
            }

            $channels[] = (object) [
                'id' => (int) $id,
                'code' => $code,
                'hostname' => $hostname,
            ];
        }

        return $channels;
    }
}
