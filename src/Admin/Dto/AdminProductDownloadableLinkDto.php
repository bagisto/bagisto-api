<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Schema-only DTO for a downloadable product link row.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductDownloadableLinkDto
{
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;

    #[ApiProperty(writable: false)]
    public ?int $downloads = null;

    #[ApiProperty(writable: false)]
    public ?string $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedPrice = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $file = null;

    #[ApiProperty(writable: false)]
    public ?string $fileUrl = null;

    #[ApiProperty(writable: false)]
    public ?string $sampleFile = null;

    #[ApiProperty(writable: false)]
    public ?string $sampleFileUrl = null;

    #[ApiProperty(writable: false)]
    public ?string $sampleType = null;

    /** @var array<int, mixed>|null  [{locale, title}] */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;
}
