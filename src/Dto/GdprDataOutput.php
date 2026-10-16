<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class GdprDataOutput
{
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public array $customer = [];

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public array $addresses = [];

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public array $orders = [];
}
