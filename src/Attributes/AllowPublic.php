<?php

namespace Webkul\BagistoApi\Attributes;

use Attribute;

/**
 * Mark a GraphQL operation as public (no X-STOREFRONT-KEY required)
 *
 * @see RequiresStorefrontKey
 */
#[Attribute(Attribute::TARGET_METHOD)]
class AllowPublic
{
    public function __construct(
        public ?string $description = null,
        public bool $rateLimitByIp = false,
    ) {}
}
