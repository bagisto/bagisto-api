<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for the `createAdminPlaceOrder` GraphQL mutation.
 *
 * REST path: POST /api/admin/orders/place/{cartId}  (cartId from URL)
 */
class AdminPlaceOrderInput
{
    #[ApiProperty(description: 'Draft cart ID to finalise into an order.')]
    #[Groups(['mutation'])]
    public ?int $cartId = null;
}
