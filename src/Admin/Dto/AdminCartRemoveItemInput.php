<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class AdminCartRemoveItemInput
{
    /**
     * The cart id.
     */
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    /**
     * The cart item id.
     */
    #[Groups(['mutation'])]
    public ?int $cartItemId = null;
}
