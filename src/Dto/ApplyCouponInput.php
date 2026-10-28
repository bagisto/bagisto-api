<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class ApplyCouponInput
{
    /**
     * The code.
     */
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * Get code.
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Set code.
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }
}
