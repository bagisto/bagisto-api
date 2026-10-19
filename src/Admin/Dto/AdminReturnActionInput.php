<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminReturnActionInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Return (RMA) IRI, e.g. /api/admin/rma/requests/12')]
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
    public function setId(?string $value): void
    {
        $this->id = $value;
    }
}
