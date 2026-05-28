<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingSitemap;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Sitemap\Models\Sitemap;

class AdminMarketingSitemapItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.sitemap.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Sitemap::find($id);
    }

    protected function mapToDto(object $sitemap): AdminMarketingSitemap
    {
        /** @var Sitemap $sitemap */
        $dto = new AdminMarketingSitemap;

        $dto->id = (int) $sitemap->id;
        $dto->fileName = $sitemap->file_name;
        $dto->path = $sitemap->path;
        $dto->generatedAt = $sitemap->generated_at ? \Carbon\Carbon::parse($sitemap->generated_at)->toIso8601String() : null;

        $additional = $sitemap->additional ?? [];
        $dto->indexFile = $additional['index'] ?? null;
        $dto->generatedSitemaps = $additional['sitemaps'] ?? [];

        $dto->createdAt = $sitemap->created_at?->toIso8601String();
        $dto->updatedAt = $sitemap->updated_at?->toIso8601String();

        return $dto;
    }

    /** Public alias used by the processor to reuse mapping after a write. */
    public function mapToDtoPublic(object $sitemap): AdminMarketingSitemap
    {
        return $this->mapToDto($sitemap);
    }
}
