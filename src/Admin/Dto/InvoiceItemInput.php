<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * One line in an Admin Invoice/Refund create body — `{ orderItemId, quantity }`.
 *
 * Decorated `operations: [], graphQlOperations: []` so it registers as a
 * GraphQL input type without exposing any REST/GraphQL endpoints of its own
 * (per the nested-DTO rule in CLAUDE.md).
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class InvoiceItemInput
{
    #[ApiProperty]
    public ?int $orderItemId = null;

    #[ApiProperty]
    public ?int $quantity = null;
}
