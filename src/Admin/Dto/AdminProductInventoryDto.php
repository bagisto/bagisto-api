<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Schema-only DTO for a per-source inventory entry embedded inside
 * AdminCatalogProduct detail.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductInventoryDto
{
    #[ApiProperty(writable: false)]
    public ?int $sourceId = null;

    #[ApiProperty(writable: false)]
    public ?string $sourceCode = null;

    #[ApiProperty(writable: false)]
    public ?int $qty = null;
}
