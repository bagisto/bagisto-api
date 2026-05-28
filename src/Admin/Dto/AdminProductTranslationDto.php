<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Schema-only DTO for a single locale translation row embedded inside
 * AdminCatalogProduct detail. No REST/GraphQL operations — registered purely
 * so API Platform includes it as a type in the schema.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminProductTranslationDto
{
    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $shortDescription = null;

    #[ApiProperty(writable: false)]
    public ?string $urlKey = null;

    #[ApiProperty(writable: false)]
    public ?string $metaTitle = null;

    #[ApiProperty(writable: false)]
    public ?string $metaDescription = null;

    #[ApiProperty(writable: false)]
    public ?string $metaKeywords = null;
}
