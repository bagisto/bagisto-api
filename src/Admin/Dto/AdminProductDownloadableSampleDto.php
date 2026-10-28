<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductDownloadableSampleDto
{
    /**
     * The id.
     */
    #[ApiProperty(writable: false)]
    public ?int $id = null;

    /**
     * The sort order.
     */
    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;

    /**
     * The type.
     */
    #[ApiProperty(writable: false)]
    public ?string $type = null;

    /**
     * The file.
     */
    #[ApiProperty(writable: false)]
    public ?string $file = null;

    /**
     * The file url.
     */
    #[ApiProperty(writable: false)]
    public ?string $fileUrl = null;

    /**
     * The per-locale titles of the downloadable sample.
     *
     * @var array<int, mixed>|null [{locale, title}]
     */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;
}
