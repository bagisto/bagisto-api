<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerReviewMassUpdateStatusInput
{
    /** @var int[]|null */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    #[ApiProperty(description: 'pending|approved|disapproved')]
    #[Groups(['mutation'])]
    public ?string $value = null;
}
