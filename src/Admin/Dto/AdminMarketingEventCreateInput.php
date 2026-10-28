<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingEventCreateInput
{
    /**
     * The name.
     */
    #[ApiProperty(description: 'Event display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(description: 'Long-form description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The date.
     */
    #[ApiProperty(description: 'Event trigger date (Y-m-d).')]
    #[Groups(['mutation'])]
    public ?string $date = null;
}
