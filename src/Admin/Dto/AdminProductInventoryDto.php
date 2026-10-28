<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductInventoryDto
{
    /**
     * The source id.
     */
    #[ApiProperty(writable: false)]
    public ?int $sourceId = null;

    /**
     * The source code.
     */
    #[ApiProperty(writable: false)]
    public ?string $sourceCode = null;

    /**
     * The qty.
     */
    #[ApiProperty(writable: false)]
    public ?int $qty = null;
}
