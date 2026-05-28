<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/marketing/sitemaps/{id} and the delete mutation.
 */
class AdminMarketingSitemapUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/sitemaps/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $file_name = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $path = null;
}
