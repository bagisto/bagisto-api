<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CustomerReturnActionInput
{
    #[ApiProperty(description: 'Return (RMA) IRI, e.g. /api/shop/returns/12')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }
}
