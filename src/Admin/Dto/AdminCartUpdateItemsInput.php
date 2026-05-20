<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input for PUT /api/admin/carts/{id}/items and the GraphQL
 * createAdminCartUpdateItems mutation.
 *
 * `qty` mirrors the monolith shape: a map of cart_item_id => new quantity.
 *   { "qty": { "12": 3, "13": 1 } }
 *
 * The processor falls back to request()->all() so any additional keys
 * Cart::updateItems() consumes are forwarded.
 */
class AdminCartUpdateItemsInput
{
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    /** Map of cart_item_id => new qty. */
    #[Groups(['mutation'])]
    public ?array $qty = null;
}
