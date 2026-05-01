<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class CustomizableOptionPriceDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $label = null,
        public ?float $price = null,
        public ?string $price_type = null,
        public ?int $sort_order = null,
    ) {}
}
