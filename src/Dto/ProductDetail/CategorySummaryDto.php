<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class CategorySummaryDto
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public ?int $id = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?string $slug = null,
        public ?string $url_path = null,
    ) {}
}
