<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductCategoryRefDto
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The slug.
     */
    #[ApiProperty(writable: false)]
    public ?string $slug = null;
}
