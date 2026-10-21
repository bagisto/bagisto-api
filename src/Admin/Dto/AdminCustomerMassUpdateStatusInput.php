<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerMassUpdateStatusInput
{
    /**
     * The ids of the customers whose status is updated.
     *
     * @var int[]|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    /**
     * The value.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $value = null;
}
