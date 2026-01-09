<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: []
)]
class ThemeCustomizationTranslation extends \Webkul\Theme\Models\ThemeCustomizationTranslation
{
    protected $casts = [
        'options' => 'string',
    ];
}
