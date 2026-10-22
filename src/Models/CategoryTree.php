<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\State\CategoryTreeProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new GetCollection(
            uriTemplate: '/category-trees',
            provider: CategoryTreeProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['CategoryTree'],
                summary: 'Get hierarchical category tree structure',
                description: 'Returns categories as a nested tree. Pass parentId to scope results to children of that category. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'Nested category tree.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 24,
                                        'position' => 1,
                                        'status' => 1,
                                        'displayMode' => 'products_and_description',
                                        '_lft' => 27,
                                        '_rgt' => 32,
                                        'createdAt' => '2026-05-21T12:53:40+05:30',
                                        'updatedAt' => '2026-05-21T12:53:40+05:30',
                                        'url' => '',

                                        'children' => [
                                            [
                                                'id' => 25,
                                                'position' => 1,
                                                'status' => 1,
                                                'displayMode' => 'products_and_description',
                                                '_lft' => 28,
                                                '_rgt' => 29,
                                                'createdAt' => '2026-05-21T12:53:40+05:30',
                                                'updatedAt' => '2026-05-21T12:53:40+05:30',
                                                'url' => '',
                                                'children' => [],
                                            ], [
                                                'id' => 26,
                                                'position' => 2,
                                                'status' => 1,
                                                'displayMode' => 'products_and_description',
                                                '_lft' => 30,
                                                '_rgt' => 31,
                                                'createdAt' => '2026-05-21T12:53:40+05:30',
                                                'updatedAt' => '2026-05-21T12:53:40+05:30',
                                                'url' => '',
                                                'children' => [],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
                parameters: [
                    new Parameter(
                        name: 'parentId',
                        in: 'query',
                        description: 'Return children of this category ID. Omit to return root categories.',
                        required: false,
                        schema: [
                            'type' => 'integer',
                            'example' => 1,
                        ],
                    ),
                    new Parameter(
                        name: 'depth',
                        in: 'query',
                        description: 'Maximum nesting depth (default: 4).',
                        required: false,
                        schema: [
                            'type' => 'integer',
                            'default' => 4,
                            'example' => 4,
                        ],
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: CategoryTreeProvider::class,
            paginationEnabled: false,
            description: 'The current channel\'s root category and its active descendants as a nested tree (up to four levels by default), ordered by position, as a plain list (not paginated). Takes no arguments. Needs the X-STOREFRONT-KEY header.',
        ),
    ],
)]
class CategoryTree
{
    /**
     * This class doesn't need any properties as it's just a DTO
     * The data is provided by the CategoryTreeProvider.
     */
}
