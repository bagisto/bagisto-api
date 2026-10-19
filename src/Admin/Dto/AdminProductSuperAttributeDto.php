<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductSuperAttributeDto
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The code.
     */
    #[ApiProperty(writable: false)]
    public ?string $code = null;

    /**
     * The type.
     */
    #[ApiProperty(writable: false)]
    public ?string $type = null;

    /**
     * The admin name.
     */
    #[ApiProperty(writable: false)]
    public ?string $adminName = null;

    /** @var array<int, mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $options = null;
}
