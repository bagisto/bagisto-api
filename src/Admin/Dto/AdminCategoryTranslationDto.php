<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminCategoryTranslationDto
{
    /**
     * The locale.
     */
    public ?string $locale = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The slug.
     */
    public ?string $slug = null;

    /**
     * The description.
     */
    public ?string $description = null;

    /**
     * The meta title.
     */
    public ?string $metaTitle = null;

    /**
     * The meta description.
     */
    public ?string $metaDescription = null;

    /**
     * The meta keywords.
     */
    public ?string $metaKeywords = null;
}
