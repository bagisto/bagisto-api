<?php

namespace Webkul\BagistoApi\Exception;

use RuntimeException;

class SocialLoginException extends RuntimeException
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}
