<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: []
)]
class ThemeCustomizationTranslation extends \Webkul\Theme\Models\ThemeCustomizationTranslation
{
    /**
     * The casts.
     */
    protected $casts = [
        'options' => 'string',
    ];

    /**
     * Get unique translation identifier for API.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }
}
