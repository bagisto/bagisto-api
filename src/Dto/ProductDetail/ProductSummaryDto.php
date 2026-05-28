<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class ProductSummaryDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $sku = null,
        public ?string $name = null,
        public ?float $price = null,
        public ?string $formatted_price = null,
        public ?string $base_image_url = null,
    ) {}
}
