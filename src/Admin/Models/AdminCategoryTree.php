<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCategoryTreeProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCategoryTree',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/catalog/categories/tree',
            provider: AdminCategoryTreeProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'Category tree (nested)',
                description: 'Full nested category tree. Supports optional locale, status, and rootId filters. Returns a JSON array of root nodes.',
                parameters: [
                    new Model\Parameter('locale', 'query', 'Locale code for translation resolution (e.g. "en").', false, schema: [
                        'type' => 'string',
                        'example' => 'en',
                    ]),
                    new Model\Parameter('status', 'query', 'Filter by status (0 = disabled, 1 = enabled). Nodes with no qualifying descendants are pruned.', false, schema: [
                        'type' => 'integer',
                        'enum' => [0, 1],
                        'example' => 1,
                    ]),
                    new Model\Parameter('rootId', 'query', 'Limit tree to descendants of this category ID (inclusive). Returns empty array if the ID is unknown.', false, schema: [
                        'type' => 'integer',
                        'example' => 1,
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Nested category tree in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 1,
                                            'name' => 'Root Category',
                                            'slug' => 'root',
                                            'status' => 1,
                                            'position' => 0,
                                            'parentId' => null,
                                            'displayMode' => null,

                                            'children' => [
                                                [
                                                    'id' => 2,
                                                    'name' => 'Apparel',
                                                    'slug' => 'apparel',
                                                    'status' => 1,
                                                    'position' => 1,
                                                    'parentId' => 1,
                                                    'displayMode' => null,
                                                    'children' => [],
                                                ], [
                                                    'id' => 5,
                                                    'name' => 'Electronics',
                                                    'slug' => 'electronics',
                                                    'status' => 1,
                                                    'position' => 2,
                                                    'parentId' => 1,
                                                    'displayMode' => null,
                                                    'children' => [],
                                                ],
                                            ],
                                        ],
                                    ],

                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 50,
                                        'lastPage' => 1,
                                        'total' => 1,
                                        'from' => 1,
                                        'to' => 1,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCategoryTreeProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'locale' => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'rootId' => ['type' => 'Int'],
            ],
            description: 'Admin catalog categories tree (nested). Returns root nodes; each node has its full subtree under `children`.',
        ),
    ],
)]
class AdminCategoryTree
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The slug.
     */
    #[ApiProperty(writable: false)]
    public ?string $slug = null;

    /**
     * The status.
     */
    #[ApiProperty(writable: false)]
    public ?int $status = null;

    /**
     * The position.
     */
    #[ApiProperty(writable: false)]
    public ?int $position = null;

    /**
     * The parent id.
     */
    #[ApiProperty(writable: false)]
    public ?int $parent_id = null;

    /**
     * The display mode.
     */
    #[ApiProperty(writable: false)]
    public ?string $display_mode = null;

    /**
     * The child categories nested under this category.
     *
     * @var array<int, array<string, mixed>>|null Plain associative arrays — never nested DTOs, to avoid IRI serialization by API Platform.
     */
    #[ApiProperty(writable: false)]
    public ?array $children = null;
}
