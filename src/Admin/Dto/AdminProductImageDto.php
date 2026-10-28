<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductImageDto
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The path.
     */
    #[ApiProperty(writable: false)]
    public ?string $path = null;

    /**
     * The url.
     */
    #[ApiProperty(writable: false)]
    public ?string $url = null;

    /**
     * The sort order.
     */
    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;
}
