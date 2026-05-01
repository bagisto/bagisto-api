<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class ProductVideoDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $type = null,
        public ?string $path = null,
        public ?int $product_id = null,
        public ?int $position = null,
        public ?string $public_path = null,
    ) {}
}
