<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderPaymentDto
{
    /**
     * The id.
     */
    public ?int $id = null;

    /**
     * The method.
     */
    public ?string $method = null;

    /**
     * The method title.
     */
    public ?string $method_title = null;
}
