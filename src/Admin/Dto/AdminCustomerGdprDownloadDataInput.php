<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerGdprDownloadDataInput
{
    /**
     * The customer id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $customerId = null;
}
