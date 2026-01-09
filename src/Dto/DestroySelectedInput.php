<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class DestroySelectedInput
{
    #[Groups(['mutation'])]
    public ?array $ids = null;

    public function getIds(): ?array
    {
        return $this->ids;
    }

    public function setIds(?array $ids): void
    {
        $this->ids = $ids;
    }
}
