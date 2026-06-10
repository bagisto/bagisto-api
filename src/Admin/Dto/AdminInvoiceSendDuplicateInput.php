<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminInvoiceSendDuplicateInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $invoiceId = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $email = null;
}
