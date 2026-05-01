<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class BundleOptionDto
{
    /**
     * @param  BundleOptionProductDto[]  $products
     */
    public function __construct(
        public ?int $id = null,
        public ?string $type = null,
        public ?bool $is_required = null,
        public ?int $sort_order = null,
        public ?string $label = null,
        #[ApiProperty(readableLink: true)]
        public array $products = [],
    ) {}
}
