<?php

namespace Webkul\BagistoApi\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AllowPublic
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public ?string $description = null,
        public bool $rateLimitByIp = false,
    ) {}
}
