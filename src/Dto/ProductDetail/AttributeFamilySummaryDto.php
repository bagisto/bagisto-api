<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [])]
class AttributeFamilySummaryDto
{
    public function __construct(
        public ?int $id = null,
        public ?string $code = null,
        public ?string $name = null,
    ) {}
}
