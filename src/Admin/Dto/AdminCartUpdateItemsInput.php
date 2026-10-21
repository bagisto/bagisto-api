<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class AdminCartUpdateItemsInput
{
    /**
     * The cart id.
     */
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    /**
     * Map of cart_item_id => new qty.
     */
    #[Groups(['mutation'])]
    public ?array $qty = null;
}
