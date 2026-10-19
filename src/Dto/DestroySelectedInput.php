<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class DestroySelectedInput
{
    /**
     * The ids.
     */
    #[Groups(['mutation'])]
    public ?array $ids = null;

    /**
     * Get ids.
     */
    public function getIds(): ?array
    {
        return $this->ids;
    }

    /**
     * Set ids.
     */
    public function setIds(?array $ids): void
    {
        $this->ids = $ids;
    }
}
