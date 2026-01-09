<?php

namespace Webkul\BagistoApi\Dto;

/**
 * DTO for customer login input
 */
class CustomerLoginInput
{
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
    ) {}
}
