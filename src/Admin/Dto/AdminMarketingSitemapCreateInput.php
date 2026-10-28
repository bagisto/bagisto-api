<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/sitemaps + createAdminMarketingSitemap.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SitemapController::store
 * validation rules.
 */
class AdminMarketingSitemapCreateInput
{
    #[ApiProperty(description: 'File name; must end in .xml (e.g. sitemap.xml).')]
    #[Groups(['mutation'])]
    public ?string $file_name = null;

    #[ApiProperty(description: 'Storage directory path; must start and end with / (e.g. /).')]
    #[Groups(['mutation'])]
    public ?string $path = null;

    /**
     * @var array<int, int>|null
     */
    #[ApiProperty(description: 'Channel ids the sitemap covers. At least one is required — a sitemap with no channel generates nothing.')]
    #[Groups(['mutation'])]
    public ?array $channels = null;
}
