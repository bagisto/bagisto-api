<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductBundleOptionDto
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The label.
     */
    #[ApiProperty(writable: false)]
    public ?string $label = null;

    /**
     * The type.
     */
    #[ApiProperty(writable: false)]
    public ?string $type = null;

    /**
     * The position.
     */
    #[ApiProperty(writable: false)]
    public ?int $position = null;

    /**
     * The is required.
     */
    #[ApiProperty(writable: false)]
    public ?bool $isRequired = null;

    /**
     * The products offered by the bundle option.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $products = null;
}
