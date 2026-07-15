<?php

namespace Webkul\BagistoApi\Models\Filter;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    shortName: 'AttributeTranslationFilter',
    routePrefix: '/api/shop',
    operations: [],
    graphQlOperations: []
)]
class AttributeTranslation extends \Webkul\Attribute\Models\AttributeTranslation
{
    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
