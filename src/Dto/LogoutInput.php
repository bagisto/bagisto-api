<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class LogoutInput
{
    /**
     * The device token.
     */
    #[Groups(['mutation'])]
    public ?string $deviceToken = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        ?string $deviceToken = null,
    ) {
        $this->deviceToken = $deviceToken;
    }
}
