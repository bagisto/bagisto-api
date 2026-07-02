<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Webkul\BagistoApi\State\CategoryTreeProvider;

/**
 * Category Tree REST API Resource
 *
 * Provides hierarchical category tree structure via REST API
 * Endpoint: GET /api/shop/category-trees
 *
 * Query Parameters:
 * - parentId: Filter by parent category ID
 * - depth: Maximum depth to traverse (default: 4)
 */
#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new GetCollection(
            uriTemplate: '/category-trees',
            provider: CategoryTreeProvider::class,
            paginationEnabled: false,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['CategoryTree'],
                summary: 'Get hierarchical category tree structure',
                description: 'Returns categories as a nested tree. Pass parentId to scope results to children of that category. Public endpoint.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Nested category tree.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'          => 24,
                                        'position'    => 1,
                                        'status'      => 1,
                                        'displayMode' => 'products_and_description',
                                        '_lft'        => 27,
                                        '_rgt'        => 32,
                                        'createdAt'   => '2026-05-21T12:53:40+05:30',
                                        'updatedAt'   => '2026-05-21T12:53:40+05:30',
                                        'url'         => '',
                                        'children'    => [
                                            [
                                                'id'          => 25,
                                                'position'    => 1,
                                                'status'      => 1,
                                                'displayMode' => 'products_and_description',
                                                '_lft'        => 28,
                                                '_rgt'        => 29,
                                                'createdAt'   => '2026-05-21T12:53:40+05:30',
                                                'updatedAt'   => '2026-05-21T12:53:40+05:30',
                                                'url'         => '',
                                                'children'    => [],
                                            ],
                                            [
                                                'id'          => 26,
                                                'position'    => 2,
                                                'status'      => 1,
                                                'displayMode' => 'products_and_description',
                                                '_lft'        => 30,
                                                '_rgt'        => 31,
                                                'createdAt'   => '2026-05-21T12:53:40+05:30',
                                                'updatedAt'   => '2026-05-21T12:53:40+05:30',
                                                'url'         => '',
                                                'children'    => [],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
                parameters: [
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'parentId',
                        in: 'query',
                        description: 'Return children of this category ID. Omit to return root categories.',
                        required: false,
                        schema: ['type' => 'integer', 'example' => 1],
                    ),
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'depth',
                        in: 'query',
                        description: 'Maximum nesting depth (default: 4).',
                        required: false,
                        schema: ['type' => 'integer', 'default' => 4, 'example' => 4],
                    ),
                ],
            ),
        ),
    ],
)]
class CategoryTree
{
    /**
     * This class doesn't need any properties as it's just a DTO
     * The data is provided by the CategoryTreeProvider
     */
}
