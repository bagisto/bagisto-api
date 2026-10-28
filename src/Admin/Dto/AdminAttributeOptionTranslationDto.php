<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['skip_null_values' => false],
)]
class AdminAttributeOptionTranslationDto
{
    /**
     * The locale.
     */
    public ?string $locale = null;

    /**
     * The label.
     */
    public ?string $label = null;
}
