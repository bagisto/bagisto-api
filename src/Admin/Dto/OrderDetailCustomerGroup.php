<?php

namespace Webkul\BagistoApi\Admin\Dto;

/**
 * Customer-group block embedded in the order detail's customer.
 */
#[\ApiPlatform\Metadata\ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailCustomerGroup
{
    #[\ApiPlatform\Metadata\ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $code = null;

    public ?string $name = null;
}
