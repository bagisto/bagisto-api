<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class BundleOptionProductDto
{
    public function __construct(
        public ?int $id = null,
        public ?int $product_id = null,
        public ?int $qty = null,
        public ?bool $is_default = null,
        public ?int $sort_order = null,
        #[ApiProperty(readableLink: true)]
        public ?ProductSummaryDto $product = null,
    ) {}
}
