<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingSitemapGenerateInput
{
    /**
     * The sitemap id.
     */
    #[ApiProperty(description: 'Sitemap id to regenerate. REST takes it from the URL; GraphQL from this field.')]
    #[Groups(['mutation'])]
    public ?int $sitemapId = null;
}
