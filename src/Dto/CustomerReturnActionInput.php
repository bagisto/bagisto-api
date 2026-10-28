<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CustomerReturnActionInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Return (RMA) IRI, e.g. /api/shop/returns/12')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * Get id.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set id.
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }
}
