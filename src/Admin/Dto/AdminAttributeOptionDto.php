<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminAttributeOptionDto
{
    /**
     * The id.
     */
    public ?int $id = null;

    /**
     * The admin name.
     */
    public ?string $adminName = null;

    /**
     * The sort order.
     */
    public ?int $sortOrder = null;

    /**
     * The swatch value.
     */
    public ?string $swatchValue = null;

    /**
     * The swatch value url.
     */
    public ?string $swatchValueUrl = null;

    /**
     * @var array<int, AdminAttributeOptionTranslationDto>|null
     */
    public ?array $translations = null;
}
