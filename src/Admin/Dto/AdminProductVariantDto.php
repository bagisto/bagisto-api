<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Schema-only DTO for a configurable product variant (child product row).
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductVariantDto
{
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?string $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedPrice = null;

    /** @var array<string, mixed>|null key = attribute code, value = option label */
    #[ApiProperty(writable: false)]
    public ?array $attributeValues = null;

    #[ApiProperty(writable: false)]
    public ?bool $inStock = null;

    #[ApiProperty(writable: false)]
    public ?int $quantity = null;
}
