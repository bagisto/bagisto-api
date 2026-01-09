<?php

namespace Webkul\BagistoApi\Models\Filter;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\State\FilterableAttributesProvider;

#[ApiResource(
    shortName: 'CategoryAttributeFilter',
    description: 'Attribute Filters for the category or product grid page',
    routePrefix: '/api/shop',
    operations: [
        new \ApiPlatform\Metadata\GetCollection(
            uriTemplate: '/filters/attributes'
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: FilterableAttributesProvider::class,
            args: [
                'categorySlug' => ['type' => 'String', 'required' => false],
                'first'        => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last'         => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after'        => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before'       => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
            ],
        ),
    ],
)]
class Attribute extends \Webkul\BagistoApi\Models\Attribute
{
    public float $minPrice;

    public float $maxPrice;

    public function getMaxPriceAttribute(): float
    {
        return $this->maxPrice;
    }

    public function getMinPriceAttribute(): float
    {
        return $this->minPrice;
    }
}
