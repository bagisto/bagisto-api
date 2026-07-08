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

    public function getIndices(): ?array { return $this->indices; }

    public function setIndices(?array $v): void { $this->indices = $v; }
}
