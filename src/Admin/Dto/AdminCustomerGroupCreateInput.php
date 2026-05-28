<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerGroupCreateInput
{
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $name = null;
}
