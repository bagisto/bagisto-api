<?php

namespace Webkul\BagistoApi\Admin\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class AdminReorderInput
{
    /**
     * The order id.
     */
    #[Groups(['mutation'])]
    public ?string $orderId = null;
}
