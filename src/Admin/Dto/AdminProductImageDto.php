<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Schema-only DTO for a product image entry embedded inside
 * AdminCatalogProduct detail.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductImageDto
{
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $path = null;

    #[ApiProperty(writable: false)]
    public ?string $url = null;

    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;
}
