<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerMassUpdateStatusInput
{
    /** @var int[]|null */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $value = null;
}
