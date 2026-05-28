<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Schema-only DTO for a configurable product super-attribute (with options).
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductSuperAttributeDto
{
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $code = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $adminName = null;

    /** @var array<int, mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $options = null;
}
