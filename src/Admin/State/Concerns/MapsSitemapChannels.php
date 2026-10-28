<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Illuminate\Support\Facades\File;

/**
 * Channel-aware sitemap output, shared by the listing, the detail and the generate result.
 */
trait MapsSitemapChannels
{
    /**
     * The public index URL per channel — what a search engine is pointed at.
     *
     * @param  iterable<int, object>  $channels
     * @return array<int, string>
     */
    protected function sitemapUrls(iterable $channels, int $sitemapId, ?string $path, ?string $fileName): array
    {
        $urls = [];

        foreach ($channels as $channel) {
            $hostname = $this->channelBaseUrl($channel->hostname ?? null);

            if (! $hostname) {
                continue;
            }

            $urls[] = $hostname.'/storage/'.ltrim(clean_path(
                'sitemaps/'.$channel->code
                .'/'.$path
                .'/'.File::name((string) $fileName)
                .'-'.$sitemapId
                .'-'.$channel->id
                .'.'.File::extension((string) $fileName)
            ), '/');
        }

        return $urls;
    }

    /**
     * What the last generate wrote, one entry per channel.
     *
     * @param  array<string, mixed>  $additional
     * @param  iterable<int, object>  $channels
     * @return array<int, array<string, mixed>>
     */
    protected function sitemapGeneratedFiles(array $additional, iterable $channels): array
    {
        $codes = [];

        foreach ($channels as $channel) {
            $codes[(int) $channel->id] = $channel->code;
        }

        $files = [];

        foreach ($additional['channels'] ?? [] as $channelId => $generated) {
            $files[] = [
                'channelId' => (int) $channelId,
                'channelCode' => $codes[(int) $channelId] ?? null,
                'hostname' => $generated['hostname'] ?? null,
                'index' => $generated['index'] ?? null,
                'sitemaps' => $generated['sitemaps'] ?? [],
            ];
        }

        return $files;
    }

    /**
     * Normalise a channel hostname into a base URL, or null when the channel has none.
     */
    protected function channelBaseUrl(?string $hostname): ?string
    {
        $hostname = rtrim(trim((string) $hostname), '/');

        if ($hostname === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $hostname)) {
            $hostname = 'https://'.$hostname;
        }

        return $hostname;
    }
}
