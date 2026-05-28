<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/marketing/url-rewrites/{id} + delete mutation.
 */
class AdminMarketingUrlRewriteUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/url-rewrites/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $entity_type = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $request_path = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $target_path = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $redirect_type = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $locale = null;
}
