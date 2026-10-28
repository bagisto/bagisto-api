<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateGuestEuWithdrawalLinkInput
{
    #[ApiProperty(description: 'Order increment id (e.g. "1000123").')]
    #[Groups(['mutation'])]
    public ?string $order_increment_id = null;

    #[ApiProperty(description: 'Email on the guest order. The link is sent to this address.')]
    #[Groups(['mutation'])]
    public ?string $email = null;
}
