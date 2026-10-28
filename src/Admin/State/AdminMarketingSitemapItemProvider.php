<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSitemap;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\MapsSitemapChannels;
use Webkul\Sitemap\Models\Sitemap;

class AdminMarketingSitemapItemProvider extends AbstractAdminItemProvider
{
    use MapsSitemapChannels;

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.sitemap.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Sitemap::with('channels')->find($id);
    }

    protected function mapToDto(object $sitemap): AdminMarketingSitemap
    {
        /** @var Sitemap $sitemap */
        $dto = new AdminMarketingSitemap;

        $dto->id = (int) $sitemap->id;
        $dto->fileName = $sitemap->file_name;
        $dto->path = $sitemap->path;
        $dto->generatedAt = $sitemap->generated_at ? Carbon::parse($sitemap->generated_at)->toIso8601String() : null;

        $channels = $sitemap->channels;

        $dto->channels = $channels->pluck('id')->map('intval')->all();
        $dto->urls = $this->sitemapUrls($channels, (int) $sitemap->id, $sitemap->path, $sitemap->file_name);

        $additional = $sitemap->additional ?? [];

        $dto->generatedFiles = $this->sitemapGeneratedFiles($additional, $channels);
        $dto->indexFile = $additional['index'] ?? null;
        $dto->generatedSitemaps = $additional['sitemaps'] ?? [];

        $dto->createdAt = $sitemap->created_at?->toIso8601String();
        $dto->updatedAt = $sitemap->updated_at?->toIso8601String();

        return $dto;
    }

    public function mapToDtoPublic(object $sitemap): AdminMarketingSitemap
    {
        return $this->mapToDto($sitemap);
    }
}
