<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class VariantSummaryDto
{
    /**
     * @param  array<string,int>  $super_attribute_options
     */
    public function __construct(
        public ?int $id = null,
        public ?string $sku = null,
        public ?string $name = null,
        public ?float $price = null,
        public ?string $formatted_price = null,
        public ?bool $is_saleable = null,
        public ?string $base_image_url = null,
        public array $super_attribute_options = [],
    ) {}
}
