<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminCancelOrderInput
{
    /**
     * The order id.
     */
    #[ApiProperty(description: 'Order id to cancel.')]
    #[Groups(['mutation'])]
    public ?int $orderId = null;
}
