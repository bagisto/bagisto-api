<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaCustomFieldMassUpdateStatusInput
{
    /** @var array<int,int>|null */
    #[ApiProperty(description: 'RMA custom field ids')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    #[ApiProperty(description: 'New status: 0 or 1')]
    #[Groups(['mutation'])]
    public ?int $value = null;

    public function getValue(): ?int { return $this->value; }

    public function setValue(?int $v): void { $this->value = $v; }

    public function getIndices(): ?array { return $this->indices; }

    public function setIndices(?array $v): void { $this->indices = $v; }
}
