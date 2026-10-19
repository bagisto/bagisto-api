<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class AdminCartCouponInput
{
    /**
     * The cart id.
     */
    #[Groups(['mutation'])]
    public ?string $cartId = null;

    /**
     * The code.
     */
    #[Groups(['mutation'])]
    public ?string $code = null;
}
