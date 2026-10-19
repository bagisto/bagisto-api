<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductTranslationDto
{
    /**
     * The locale.
     */
    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(writable: false)]
    public ?string $description = null;

    /**
     * The short description.
     */
    #[ApiProperty(writable: false)]
    public ?string $shortDescription = null;

    /**
     * The url key.
     */
    #[ApiProperty(writable: false)]
    public ?string $urlKey = null;

    /**
     * The meta title.
     */
    #[ApiProperty(writable: false)]
    public ?string $metaTitle = null;

    /**
     * The meta description.
     */
    #[ApiProperty(writable: false)]
    public ?string $metaDescription = null;

    /**
     * The meta keywords.
     */
    #[ApiProperty(writable: false)]
    public ?string $metaKeywords = null;
}
