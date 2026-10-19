<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductVariantDto
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The sku.
     */
    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The status.
     */
    #[ApiProperty(writable: false)]
    public ?int $status = null;

    /**
     * The price.
     */
    #[ApiProperty(writable: false)]
    public ?string $price = null;

    /**
     * The formatted price.
     */
    #[ApiProperty(writable: false)]
    public ?string $formattedPrice = null;

    /** @var array<string, mixed>|null key = attribute code, value = option label */
    #[ApiProperty(writable: false)]
    public ?array $attributeValues = null;

    /**
     * The in stock.
     */
    #[ApiProperty(writable: false)]
    public ?bool $inStock = null;

    /**
     * The quantity.
     */
    #[ApiProperty(writable: false)]
    public ?int $quantity = null;
}
