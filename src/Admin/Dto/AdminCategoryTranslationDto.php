<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;

/**
 * One row of category_translations, surfaced inline on the detail response.
 *
 * Annotated with empty operations / graphQlOperations so API Platform registers
 * it as a schema type (needed for GraphQL nested-object validation) without
 * exposing CRUD endpoints.
 */
#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminCategoryTranslationDto
{
    public ?string $locale = null;

    public ?string $name = null;

    public ?string $slug = null;

    public ?string $description = null;

    public ?string $metaTitle = null;

    public ?string $metaDescription = null;

    public ?string $metaKeywords = null;
}
