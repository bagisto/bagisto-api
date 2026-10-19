<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminAttributeTranslationDto
{
    /**
     * The locale.
     */
    public ?string $locale = null;

    /**
     * The name.
     */
    public ?string $name = null;
}
