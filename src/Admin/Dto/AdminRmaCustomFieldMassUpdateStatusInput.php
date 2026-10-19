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

    /**
     * The value.
     */
    #[ApiProperty(description: 'New status: 0 or 1')]
    #[Groups(['mutation'])]
    public ?int $value = null;

    /**
     * Get value.
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * Set value.
     */
    public function setValue(?int $value): void
    {
        $this->value = $value;
    }

    /**
     * Get indices.
     */
    public function getIndices(): ?array
    {
        return $this->indices;
    }

    /**
     * Set indices.
     */
    public function setIndices(?array $value): void
    {
        $this->indices = $value;
    }
}
