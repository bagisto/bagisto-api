<?php

namespace Webkul\BagistoApi\Dto;

class CustomerLoginInput
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
    ) {}
}
