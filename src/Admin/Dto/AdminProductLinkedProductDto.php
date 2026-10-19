<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductLinkedProductDto
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The associated product id.
     */
    #[ApiProperty(writable: false)]
    public ?int $associatedProductId = null;

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
     * The qty.
     */
    #[ApiProperty(writable: false)]
    public ?int $qty = null;

    /**
     * The sort order.
     */
    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;
}
