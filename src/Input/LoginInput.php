<?php

namespace Webkul\BagistoApi\Input;

class LoginInput
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
    ) {}
}
