<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminInvoiceSendDuplicateInput
{
    /**
     * The invoice id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $invoiceId = null;

    /**
     * The email.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $email = null;
}
