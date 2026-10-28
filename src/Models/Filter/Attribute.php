<?php

namespace Webkul\BagistoApi\Models\Filter;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\State\FilterableAttributesProvider;

#[ApiResource(
    shortName: 'CategoryAttributeFilter',
    description: 'Attribute Filters for the category or product grid page',
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/categories/attributes',
            provider: FilterableAttributesProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Category'],
                summary: 'List the filterable attributes of a category',
                description: 'Returns the filterable attributes for the layered navigation of a category, with their options and the price range of its products (converted to the requested currency). When the category has no filterable attributes of its own, or no category is given, every globally filterable attribute is returned. Public endpoint.',
                parameters: [
                    new Model\Parameter(
                        name: 'category_id',
                        in: 'query',
                        required: false,
                        description: 'Category ID. Returns 404 when the category does not exist.',
                        schema: ['type' => 'integer', 'example' => 2],
                    ),
                    new Model\Parameter(
                        name: 'category_slug',
                        in: 'query',
                        required: false,
                        description: 'Category slug, used when `category_id` is not sent.',
                        schema: ['type' => 'string', 'example' => 'men'],
                    ),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Filterable attributes.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 23,
                                        'code' => 'color',
                                        'adminName' => 'Color',
                                        'type' => 'select',
                                        'isFilterable' => 1,
                                        'swatchType' => 'color',
                                        'minPrice' => 0,
                                        'maxPrice' => 120,
                                        'options' => [
                                            [
                                                'id' => 1,
                                                'adminName' => 'Red',
                                                'sortOrder' => 1,
                                                'swatchValue' => '#ff0000',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Category not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: FilterableAttributesProvider::class,
            args: [
                'categoryId' => ['type' => 'Int', 'description' => 'Category ID; takes precedence over categorySlug'],
                'categorySlug' => ['type' => 'String', 'required' => false],
                'first' => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last' => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after' => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before' => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
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
