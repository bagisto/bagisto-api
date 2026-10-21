<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerReviewMassUpdateStatusInput
{
    /**
     * The ids of the product reviews whose status is updated.
     *
     * @var int[]|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    /**
     * The value.
     */
    #[ApiProperty(description: 'pending|approved|disapproved')]
    #[Groups(['mutation'])]
    public ?string $value = null;
}
