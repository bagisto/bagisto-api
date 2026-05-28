<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerNoteCreateInput
{
    #[ApiProperty(description: 'Target customer id (path arg, also accepted in input).')]
    #[Groups(['mutation'])]
    public ?int $customer_id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $note = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?bool $customer_notified = null;
}
