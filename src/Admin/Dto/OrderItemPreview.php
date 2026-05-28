<?php

namespace Webkul\BagistoApi\Admin\Dto;

/**
 * Slim line-item preview for the Orders listing "Items" badge.
 *
 * Only enough to render the badge (name, qty, thumbnail) — the full order
 * item payload is served by the items sub-resource, not the listing.
 *
 * Property names are camelCase: this is a plain nested DTO and the API
 * Platform name converter does not reach it, so the property name is the
 * output key verbatim.
 */
#[\ApiPlatform\Metadata\ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderItemPreview
{
    #[\ApiPlatform\Metadata\ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $sku = null;

    public ?string $name = null;

    public ?int $qtyOrdered = null;

    public ?string $productImage = null;
}
