<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for DELETE /api/admin/carts/{id}/items (with body) and the GraphQL
 * createAdminCartRemoveItem mutation.
 */
class AdminCartRemoveItemInput
{
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    #[Groups(['mutation'])]
    public ?int $cartItemId = null;
}
