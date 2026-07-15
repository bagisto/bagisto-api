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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingEventCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingEventUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminMarketingEventCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingEventItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingEventProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingEventWriteProvider;

/**
 * Admin Marketing → Events endpoints (Block F2b).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Communications\EventController 1:1.
 *
 * REST:
 *   GET    /api/admin/marketing/events
 *   GET    /api/admin/marketing/events/{id}
 *   POST   /api/admin/marketing/events
 *   PUT    /api/admin/marketing/events/{id}
 *   DELETE /api/admin/marketing/events/{id}
 *
 * GraphQL:
 *   adminMarketingEvents             — cursor listing
 *   adminMarketingEvent(id:)         — detail
 *   createAdminMarketingEvent
 *   updateAdminMarketingEvent
 *   deleteAdminMarketingEvent
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingEvent',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/events',
            input: AdminMarketingEventCreateInput::class,
            processor: AdminMarketingEventProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Create a marketing event',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'description', 'date'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'Holiday Sale Kickoff'],
                                    'description' => ['type' => 'string', 'example' => 'Email blast to all subscribers.'],
                                    'date' => ['type' => 'string', 'example' => '2026-12-20'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Marketing event created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 14,
                                    'name' => 'Holiday Sale Kickoff',
                                    'description' => 'Email blast to all subscribers.',
                                    'date' => '2026-12-20',
                                    'createdAt' => '2026-05-28T10:57:24+05:30',
                                    'updatedAt' => '2026-05-28T10:57:24+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/events/{id}',
            input: AdminMarketingEventUpdateInput::class,
            provider: AdminMarketingEventWriteProvider::class,
            processor: AdminMarketingEventProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Update a marketing event',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'Holiday Sale Kickoff'],
                                    'description' => ['type' => 'string', 'example' => 'Email blast to all subscribers.'],
                                    'date' => ['type' => 'string', 'example' => '2026-08-01'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Marketing event updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 14,
                                    'name' => 'Holiday Sale Kickoff',
                                    'description' => 'Email blast to all subscribers.',
                                    'date' => '2026-08-01',
                                    'createdAt' => '2026-05-28T10:57:24+05:30',
                                    'updatedAt' => '2026-06-10T09:20:11+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Marketing event not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/events/{id}',
            provider: AdminMarketingEventWriteProvider::class,
            processor: AdminMarketingEventProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Delete a marketing event',
                responses: [
                    '200' => new Model\Response(
                        description: 'Marketing event deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Marketing event deleted.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Marketing event not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/events/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingEventItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Marketing event detail',
                responses: [
                    '200' => new Model\Response(
                        description: 'Single marketing event.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 14,
                                    'name' => 'Holiday Sale Kickoff',
                                    'description' => 'Email blast to all subscribers.',
                                    'date' => '2026-12-20',
                                    'createdAt' => '2026-05-28T10:57:24+05:30',
                                    'updatedAt' => '2026-05-28T10:57:24+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Marketing event not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/events',
            provider: AdminMarketingEventCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'List marketing events',
                description: 'Filters: name (LIKE), date_from, date_to. Sort: id (default desc), name, date.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('date_from', 'query', 'Event date range lower bound (Y-m-d).', false, schema: ['type' => 'string']),
                    new Model\Parameter('date_to', 'query', 'Event date range upper bound (Y-m-d).', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'name', 'date']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 14,
                                            'name' => 'Holiday Sale Kickoff',
                                            'description' => 'Email blast to all subscribers.',
                                            'date' => '2026-12-20',
                                            'createdAt' => '2026-05-28T10:57:24+05:30',
                                            'updatedAt' => '2026-05-28T10:57:24+05:30',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 1,
                                        'total' => 3,
                                        'from' => 1,
                                        'to' => 3,
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
            provider: AdminMarketingEventCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name' => ['type' => 'String'],
                'date_from' => ['type' => 'String'],
                'date_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin marketing events listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingEventItemProvider::class,
            description: 'Admin marketing event detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingEventCreateInput::class,
            processor: AdminMarketingEventProcessor::class,
            description: 'Create a marketing event. Becomes createAdminMarketingEvent.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingEventUpdateInput::class,
            processor: AdminMarketingEventProcessor::class,
            description: 'Update a marketing event. Becomes updateAdminMarketingEvent.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingEventUpdateInput::class,
            processor: AdminMarketingEventProcessor::class,
            description: 'Delete a marketing event. Becomes deleteAdminMarketingEvent.',
        ),
    ],
)]
class AdminMarketingEvent
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $date = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
