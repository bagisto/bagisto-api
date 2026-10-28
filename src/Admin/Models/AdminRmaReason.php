<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaReasonUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaReasonWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaReason',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/reasons',
            provider: AdminRmaReasonCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'List RMA reasons',
                description: 'Return/cancel reasons the RMA form offers. Filters: title (LIKE), status. Sort: id (default desc), position, title.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA reasons.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 2,
                                            'title' => 'Damaged product',
                                            'status' => 1,
                                            'position' => 1,
                                            'isAdmin' => 0,
                                            'resolutionType' => ['return', 'cancel_items'],
                                            'message' => null,
                                            'createdAt' => '2026-07-20T09:00:00+00:00',
                                            'updatedAt' => '2026-07-20T09:00:00+00:00',
                                        ],
                                    ],

                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
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
        new Get(
            uriTemplate: '/rma/reasons/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaReasonItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Get an RMA reason',
                description: 'One RMA reason by ID with its resolution types. An unknown ID is a 404.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA reason.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 2,
                                    'title' => 'Damaged product',
                                    'status' => 1,
                                    'position' => 1,
                                    'isAdmin' => 0,
                                    'resolutionType' => ['return', 'cancel_items'],
                                    'message' => null,
                                    'createdAt' => '2026-07-20T09:00:00+00:00',
                                    'updatedAt' => '2026-07-20T09:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/rma/reasons',
            input: AdminRmaReasonCreateInput::class,
            processor: AdminRmaReasonProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Create an RMA reason',
                description: 'Permission: sales.rma.reasons.create.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['title', 'resolution_type'],

                                'properties' => [
                                    'title' => [
                                        'type' => 'string',
                                        'example' => 'Damaged product',
                                    ],

                                    'status' => [
                                        'type' => 'integer',
                                        'enum' => [0, 1],
                                        'example' => 1,
                                    ],

                                    'position' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                    ],

                                    'resolution_type' => [
                                        'type' => 'array',

                                        'items' => [
                                            'type' => 'string',
                                            'enum' => ['return', 'cancel_items'],
                                        ],

                                        'example' => ['return', 'cancel_items'],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'The created RMA reason.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 6,
                                    'title' => 'Damaged product',
                                    'status' => 1,
                                    'position' => 1,
                                    'isAdmin' => 0,
                                    'resolutionType' => ['return', 'cancel_items'],
                                    'message' => null,
                                    'createdAt' => '2026-07-20T09:00:00+00:00',
                                    'updatedAt' => '2026-07-20T09:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/rma/reasons/{id}',
            requirements: ['id' => '\d+'],
            input: AdminRmaReasonUpdateInput::class,
            provider: AdminRmaReasonWriteProvider::class,
            processor: AdminRmaReasonProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Update an RMA reason',
                description: 'Partial update. Permission: sales.rma.reasons.edit.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',

                                'properties' => [
                                    'title' => [
                                        'type' => 'string',
                                        'example' => 'Damaged on arrival',
                                    ],

                                    'status' => [
                                        'type' => 'integer',
                                        'enum' => [0, 1],
                                        'example' => 1,
                                    ],

                                    'position' => [
                                        'type' => 'integer',
                                        'example' => 2,
                                    ],

                                    'resolution_type' => [
                                        'type' => 'array',

                                        'items' => [
                                            'type' => 'string',
                                            'enum' => ['return', 'cancel_items'],
                                        ],

                                        'example' => ['return'],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The updated RMA reason.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 2,
                                    'title' => 'Damaged on arrival',
                                    'status' => 1,
                                    'position' => 2,
                                    'isAdmin' => 0,
                                    'resolutionType' => ['return'],
                                    'message' => null,
                                    'createdAt' => '2026-07-20T09:00:00+00:00',
                                    'updatedAt' => '2026-07-20T11:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/rma/reasons/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaReasonWriteProvider::class,
            processor: AdminRmaReasonProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Delete an RMA reason',
                description: 'Permission: sales.rma.reasons.delete.',
                responses: [
                    '204' => new Model\Response(description: 'The RMA reason was deleted. No body is returned.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.rma.reasons.delete.'),
                    '404' => new Model\Response(description: 'The RMA reason was not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminRmaReasonCollectionProvider::class,
            paginationType: 'cursor',
            description: 'The return and cancel reasons the RMA form offers, each with its resolution types. Filters: `title` (partial match), `status` (0 or 1). Sortable by id, position or title. `sort` names a column (or `column-asc`/`column-desc`) and `order` is `asc` or `desc` (default `desc`). Paginate with `first` (page size, default 10, max 50; 0 or less falls back to 10) and `after` (the previous page\'s `endCursor`, sent with the same `first`); `last` and `before` are not supported. Needs the `sales.rma.reasons` permission.',
            args: [
                'title' => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
        new Query(
            provider: AdminRmaReasonItemProvider::class,
            description: 'One RMA reason with its resolution types. Needs the `sales.rma.reasons` permission.',
        ),
        new Mutation(name: 'create', input: AdminRmaReasonCreateInput::class, processor: AdminRmaReasonProcessor::class),
        new Mutation(name: 'update', input: AdminRmaReasonUpdateInput::class, processor: AdminRmaReasonProcessor::class),
        new Mutation(name: 'delete', input: AdminRmaReasonUpdateInput::class, processor: AdminRmaReasonProcessor::class),
    ],
)]
class AdminRmaReason
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The title.
     */
    public ?string $title = null;

    /**
     * The status.
     */
    public ?int $status = null;

    /**
     * The position.
     */
    public ?int $position = null;

    /**
     * The is admin.
     */
    public ?int $is_admin = null;

    /**
     * The resolutions the RMA reason allows: return, cancel_items or both.
     *
     * @var array<int,string>|null
     */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $resolution_type = null;

    /**
     * The message.
     */
    public ?string $message = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;
}
