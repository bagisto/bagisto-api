<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderPaymentDto
{
    public ?int $id = null;

    public ?string $method = null;

    public ?string $method_title = null;
}
