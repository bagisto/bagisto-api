<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Schema-only DTO for a downloadable product sample row.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductDownloadableSampleDto
{
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $file = null;

    #[ApiProperty(writable: false)]
    public ?string $fileUrl = null;

    /** @var array<int, mixed>|null  [{locale, title}] */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;
}
