<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: []
)]
class SectionTranslation extends \Webkul\Theme\Models\SectionTranslation
{
    protected $casts = [
        'options' => 'string',
    ];

    /**
     * Unpublished edits never leave the storefront surface.
     *
     * @var list<string>
     */
    protected $hidden = [
        'draft_options',
    ];

    /**
     * Get unique translation identifier for API
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }
}
