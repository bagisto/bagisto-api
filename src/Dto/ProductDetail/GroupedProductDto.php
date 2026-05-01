<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class GroupedProductDto
{
    public function __construct(
        public ?int $id = null,
        public ?int $associated_product_id = null,
        public ?float $qty = null,
        public ?int $sort_order = null,
        #[ApiProperty(readableLink: true)]
        public ?ProductSummaryDto $product = null,
    ) {}
}
