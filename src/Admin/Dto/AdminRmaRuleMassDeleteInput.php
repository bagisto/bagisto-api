<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaRuleMassDeleteInput
{
    /** @var array<int,int>|null */
    #[ApiProperty(description: 'RMA rule ids')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

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
