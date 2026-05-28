<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;

/**
 * One row of attribute_translations, surfaced inline on the attribute detail response.
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
class AdminAttributeTranslationDto
{
    public ?string $locale = null;

    public ?string $name = null;
}
