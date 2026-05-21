<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for POST /api/admin/carts/{id}/payment-methods and the
 * `createAdminCartSetPaymentMethod` GraphQL mutation.
 */
class AdminCartSetPaymentMethodInput
{
    #[ApiProperty(description: 'Cart ID (GraphQL only — REST takes it from the URL).')]
    #[Groups(['mutation'])]
    public ?int $cartId = null;

    #[ApiProperty(description: 'Payment method code, e.g. "cashondelivery", "moneytransfer".')]
    #[Groups(['mutation'])]
    public ?string $method = null;
}
