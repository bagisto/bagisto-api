<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminRefundCreateInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $orderId = null;

    #[ApiProperty(description: 'Items to refund: array of { orderItemId, quantity }.')]
    #[Groups(['mutation'])]
    public array $items = [];

    #[ApiProperty(description: 'Amount of original shipping to refund (in base currency).')]
    #[Groups(['mutation'])]
    public ?float $shipping = 0.0;

    #[ApiProperty(description: 'Positive adjustment added to the refund total.')]
    #[Groups(['mutation'])]
    public ?float $adjustmentRefund = 0.0;

    #[ApiProperty(description: 'Fee subtracted from the refund total.')]
    #[Groups(['mutation'])]
    public ?float $adjustmentFee = 0.0;
}
