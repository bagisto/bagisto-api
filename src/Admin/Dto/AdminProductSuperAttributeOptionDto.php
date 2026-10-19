<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductSuperAttributeOptionDto
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The admin name.
     */
    #[ApiProperty(writable: false)]
    public ?string $adminName = null;

    /**
     * The swatch value.
     */
    #[ApiProperty(writable: false)]
    public ?string $swatchValue = null;

    /**
     * The sort order.
     */
    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;
}
