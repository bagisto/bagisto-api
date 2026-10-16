<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaStatusMassUpdateStatusInput
{
    /** @var array<int,int>|null */
    #[ApiProperty(description: 'RMA status ids')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    #[ApiProperty(description: 'New status: 0 or 1')]
    #[Groups(['mutation'])]
    public ?int $value = null;

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(?int $value): void
    {
        $this->value = $value;
    }

    public function getIndices(): ?array
    {
        return $this->indices;
    }

    public function setIndices(?array $value): void
    {
        $this->indices = $value;
    }
}
