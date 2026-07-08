<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminReturnActionInput
{
    #[ApiProperty(description: 'Return (RMA) IRI, e.g. /api/admin/rma/requests/12')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $v): void
    {
        $this->id = $v;
    }
}
