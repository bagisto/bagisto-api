<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/marketing/sitemaps/{id}/generate and the
 * createAdminMarketingSitemapGenerate GraphQL mutation.
 *
 * Takes no body parameters — only the sitemap id from the URL (REST) or
 * sitemapId field (GraphQL).
 */
class AdminMarketingSitemapGenerateInput
{
    #[ApiProperty(description: 'Sitemap id to regenerate. REST takes it from the URL; GraphQL from this field.')]
    #[Groups(['mutation'])]
    public ?int $sitemapId = null;
}
