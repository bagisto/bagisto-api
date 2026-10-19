<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class AdminCartSaveAddressInput
{
    /**
     * The cart id.
     */
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    /**
     * The billing.
     */
    #[Groups(['mutation'])]
    public ?array $billing = null;

    /**
     * The shipping.
     */
    #[Groups(['mutation'])]
    public ?array $shipping = null;
}
