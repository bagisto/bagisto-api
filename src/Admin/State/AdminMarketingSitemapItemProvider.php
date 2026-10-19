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

    /**
     * Map to dto public.
     */
    public function mapToDtoPublic(object $sitemap): AdminMarketingSitemap
    {
        return $this->mapToDto($sitemap);
    }

    /**
     * Get not found lang key.
     */
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.sitemap.not-found';
    }

    /**
     * Find entity.
     */
    protected function findEntity(int $id): ?object
    {
        return Sitemap::with('channels')->find($id);
    }

    /**
     * Map to dto.
     */
    protected function mapToDto(object $sitemap): AdminMarketingSitemap
    {
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
}
