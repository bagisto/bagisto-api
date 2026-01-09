<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * DTO for logout response
 */
class LogoutOutput
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;

    public function __construct(?bool $success = null, ?string $message = null)
    {
        $this->success = $success;
        $this->message = $message;
    }
}
