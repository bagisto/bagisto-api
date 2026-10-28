<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminPlaceOrderInput
{
    /**
     * The cart id.
     */
    #[ApiProperty(description: 'Draft cart ID to finalise into an order.')]
    #[Groups(['mutation'])]
    public ?int $cartId = null;
}
