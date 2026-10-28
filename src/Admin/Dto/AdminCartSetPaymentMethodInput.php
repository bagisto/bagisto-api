<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCartSetPaymentMethodInput
{
    /**
     * The cart id.
     */
    #[ApiProperty(description: 'Cart ID (GraphQL only — REST takes it from the URL).')]
    #[Groups(['mutation'])]
    public ?int $cartId = null;

    /**
     * The method.
     */
    #[ApiProperty(description: 'Payment method code, e.g. "cashondelivery", "moneytransfer".')]
    #[Groups(['mutation'])]
    public ?string $method = null;
}
