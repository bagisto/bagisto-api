<?php

namespace Webkul\BagistoApi\Attributes;

use Attribute;

/**
 * Mark a GraphQL operation as requiring X-STOREFRONT-KEY authentication
 *
 * @see AllowPublic
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RequiresStorefrontKey
{
    public function __construct(
        public ?string $message = null,
        public ?string $description = null,
    ) {}
}
