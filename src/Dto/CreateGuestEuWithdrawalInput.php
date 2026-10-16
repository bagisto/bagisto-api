<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateGuestEuWithdrawalInput
{
    #[ApiProperty(description: 'The signed link from the withdrawal email sent by the guest lookup. Proves the guest owns the order email.')]
    #[Groups(['mutation'])]
    public ?string $link = null;

    #[ApiProperty(description: 'Optional free-text reason (max 5000 chars).')]
    #[Groups(['mutation'])]
    public ?string $reason_text = null;
}
