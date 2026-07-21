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
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaStatusUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaStatusWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/statuses',
            provider: AdminRmaStatusCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'List RMA statuses',
                description: 'Custom return statuses. Filters: title (LIKE), status. Sort: id (default desc), title. Default statuses cannot be deleted.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA statuses.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 9,
                                            'title' => 'Awaiting inspection',
                                            'status' => 1,
                                            'color' => '#FDB022',
                                            'default' => 0,
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
            uriTemplate: '/rma/statuses/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaStatusItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Get an RMA status',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA status.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 9,
                                    'title' => 'Awaiting inspection',
                                    'status' => 1,
                                    'color' => '#FDB022',
                                    'default' => 0,
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
            uriTemplate: '/rma/statuses',
            input: AdminRmaStatusCreateInput::class,
            processor: AdminRmaStatusProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Create an RMA status',
                description: 'Title must be unique. Permission: sales.rma.statuses.create.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['title'],
                                'properties' => [
                                    'title' => ['type' => 'string', 'example' => 'Awaiting inspection'],
                                    'status' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'color' => ['type' => 'string', 'example' => '#FDB022'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'The created RMA status.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 9,
                                    'title' => 'Awaiting inspection',
                                    'status' => 1,
                                    'color' => '#FDB022',
                                    'default' => 0,
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
            uriTemplate: '/rma/statuses/{id}',
            requirements: ['id' => '\d+'],
            input: AdminRmaStatusUpdateInput::class,
            provider: AdminRmaStatusWriteProvider::class,
            processor: AdminRmaStatusProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Update an RMA status',
                description: 'Partial update. Permission: sales.rma.statuses.edit.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'title' => ['type' => 'string', 'example' => 'Inspection complete'],
                                    'status' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'color' => ['type' => 'string', 'example' => '#12B76A'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The updated RMA status.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 9,
                                    'title' => 'Inspection complete',
                                    'status' => 1,
                                    'color' => '#12B76A',
                                    'default' => 0,
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
            uriTemplate: '/rma/statuses/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaStatusWriteProvider::class,
            processor: AdminRmaStatusProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Delete an RMA status',
                description: 'Only non-default statuses can be deleted (422 otherwise). Permission: sales.rma.statuses.delete.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA status was deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'RMA status deleted successfully.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminRmaStatusCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'title' => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
        new Query(provider: AdminRmaStatusItemProvider::class),
        new Mutation(name: 'create', input: AdminRmaStatusCreateInput::class, processor: AdminRmaStatusProcessor::class),
        new Mutation(name: 'update', input: AdminRmaStatusUpdateInput::class, processor: AdminRmaStatusProcessor::class),
        new Mutation(name: 'delete', input: AdminRmaStatusUpdateInput::class, processor: AdminRmaStatusProcessor::class),
    ],
)]
class AdminRmaStatus
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $title = null;

    public ?int $status = null;

    public ?string $color = null;

    public ?int $default = null;

    public ?string $message = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
