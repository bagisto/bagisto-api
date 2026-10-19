<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCartSetShippingMethodInput
{
    /**
     * The cart id.
     */
    #[ApiProperty(description: 'Cart ID (GraphQL only — REST takes it from the URL).')]
    #[Groups(['mutation'])]
    public ?int $cartId = null;

    /**
     * The shipping method.
     */
    #[ApiProperty(description: 'Shipping method code, e.g. "flatrate_flatrate".')]
    #[Groups(['mutation'])]
    public ?string $shippingMethod = null;
}
