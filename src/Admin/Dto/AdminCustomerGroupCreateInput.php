<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerGroupCreateInput
{
    /**
     * The code.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The name.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $name = null;
}
