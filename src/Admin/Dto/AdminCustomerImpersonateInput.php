<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerImpersonateInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $customer_id = null;
}
