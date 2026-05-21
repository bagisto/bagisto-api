<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for POST /api/admin/carts/{id}/shipping-methods and the
 * `createAdminCartSetShippingMethod` GraphQL mutation.
 */
class AdminCartSetShippingMethodInput
{
    #[ApiProperty(description: 'Cart ID (GraphQL only — REST takes it from the URL).')]
    #[Groups(['mutation'])]
    public ?int $cartId = null;

    #[ApiProperty(description: 'Shipping method code, e.g. "flatrate_flatrate".')]
    #[Groups(['mutation'])]
    public ?string $shippingMethod = null;
}
