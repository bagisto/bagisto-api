<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingTemplateUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/templates/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The status.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $status = null;

    /**
     * The content.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $content = null;
}
