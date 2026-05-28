<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Schema-only DTO for a single option of a configurable super-attribute.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductSuperAttributeOptionDto
{
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $adminName = null;

    #[ApiProperty(writable: false)]
    public ?string $swatchValue = null;

    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;
}
