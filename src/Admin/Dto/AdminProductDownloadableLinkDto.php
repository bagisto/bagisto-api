<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductDownloadableLinkDto
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
     * The downloads.
     */
    #[ApiProperty(writable: false)]
    public ?int $downloads = null;

    /**
     * The price.
     */
    #[ApiProperty(writable: false)]
    public ?string $price = null;

    /**
     * The formatted price.
     */
    #[ApiProperty(writable: false)]
    public ?string $formattedPrice = null;

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
     * The sample file.
     */
    #[ApiProperty(writable: false)]
    public ?string $sampleFile = null;

    /**
     * The sample file url.
     */
    #[ApiProperty(writable: false)]
    public ?string $sampleFileUrl = null;

    /**
     * The sample type.
     */
    #[ApiProperty(writable: false)]
    public ?string $sampleType = null;

    /** @var array<int, mixed>|null  [{locale, title}] */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;
}
