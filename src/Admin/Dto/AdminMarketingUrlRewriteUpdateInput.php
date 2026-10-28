<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingUrlRewriteUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/url-rewrites/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The entity type.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $entity_type = null;

    /**
     * The request path.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $request_path = null;

    /**
     * The target path.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $target_path = null;

    /**
     * The redirect type.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $redirect_type = null;

    /**
     * The locale.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $locale = null;
}
