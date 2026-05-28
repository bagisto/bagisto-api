<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/marketing/events/{id} and the delete mutation.
 */
class AdminMarketingEventUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/events/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $date = null;
}
