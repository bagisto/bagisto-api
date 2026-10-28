<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingEventUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/marketing/events/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The date.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $date = null;
}
