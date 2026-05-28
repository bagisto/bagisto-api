<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/marketing/templates/{id} and the delete mutation.
 */
class AdminMarketingTemplateUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/templates/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $status = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $content = null;
}
