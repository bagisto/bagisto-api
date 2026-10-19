<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class GdprDataOutput
{
    /**
     * The customer.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public array $customer = [];

    /**
     * The addresses.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public array $addresses = [];

    /**
     * The orders.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public array $orders = [];
}
