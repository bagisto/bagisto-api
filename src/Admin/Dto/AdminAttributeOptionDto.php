<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;

/**
 * One attribute option, surfaced inline inside the options array on the
 * attribute detail response.
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
class AdminAttributeOptionDto
{
    public ?int $id = null;

    public ?string $adminName = null;

    public ?int $sortOrder = null;

    public ?string $swatchValue = null;

    public ?string $swatchValueUrl = null;

    /**
     * @var array<int, AdminAttributeOptionTranslationDto>|null
     */
    public ?array $translations = null;
}
