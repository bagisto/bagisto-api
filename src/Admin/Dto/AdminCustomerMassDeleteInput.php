<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerMassDeleteInput
{
    /**
     * The ids of the customers to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
