<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class SuperAttributeOptionDto
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public ?int $id = null,
        public ?string $label = null,
        public ?string $swatch_value = null,
    ) {}
}
