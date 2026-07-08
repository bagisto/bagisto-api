<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminRmaStatusMassDeleteInput
{
    /** @var array<int,int>|null */
    #[ApiProperty(description: 'RMA status ids')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    public function getIndices(): ?array { return $this->indices; }

    public function setIndices(?array $v): void { $this->indices = $v; }
}
