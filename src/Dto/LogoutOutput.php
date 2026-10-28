<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

class LogoutOutput
{
    /**
     * The success.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;

    /**
     * Create a new instance.
     */
    public function __construct(?bool $success = null, ?string $message = null)
    {
        $this->success = $success;
        $this->message = $message;
    }
}
