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
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRmaRuleUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminRmaRuleWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRmaRule',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/rules',
            provider: AdminRmaRuleCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'List RMA rules',
                description: 'Return rules (a rule sets the return window for matching products). Filters: name (LIKE), status. Sort: id (default desc), name.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA rules.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 3,
                                            'name' => 'Apparel 30-day returns',
                                            'description' => 'Return window for all clothing.',
                                            'status' => 1,
                                            'returnPeriod' => 30,
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
            uriTemplate: '/rma/rules/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaRuleItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Get an RMA rule',
                description: 'One RMA rule by ID, with the return window it sets for the products it matches. An unknown ID is a 404.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The RMA rule.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 3,
                                    'name' => 'Apparel 30-day returns',
                                    'description' => 'Return window for all clothing.',
                                    'status' => 1,
                                    'returnPeriod' => 30,
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
            uriTemplate: '/rma/rules',
            input: AdminRmaRuleCreateInput::class,
            processor: AdminRmaRuleProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Create an RMA rule',
                description: 'Permission: sales.rma.rules.create.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'return_period'],

                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'example' => 'Apparel 30-day returns',
                                    ],

                                    'description' => [
                                        'type' => 'string',
                                        'example' => 'Return window for all clothing.',
                                    ],

                                    'status' => [
                                        'type' => 'integer',
                                        'enum' => [0, 1],
                                        'example' => 1,
                                    ],

                                    'return_period' => [
                                        'type' => 'integer',
                                        'example' => 30,
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'The created RMA rule.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 3,
                                    'name' => 'Apparel 30-day returns',
                                    'description' => 'Return window for all clothing.',
                                    'status' => 1,
                                    'returnPeriod' => 30,
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
            uriTemplate: '/rma/rules/{id}',
            requirements: ['id' => '\d+'],
            input: AdminRmaRuleUpdateInput::class,
            provider: AdminRmaRuleWriteProvider::class,
            processor: AdminRmaRuleProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Update an RMA rule',
                description: 'Partial update. Permission: sales.rma.rules.edit.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',

                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'example' => 'Apparel 45-day returns',
                                    ],

                                    'description' => [
                                        'type' => 'string',
                                        'example' => 'Extended window for clothing.',
                                    ],

                                    'status' => [
                                        'type' => 'integer',
                                        'enum' => [0, 1],
                                        'example' => 1,
                                    ],

                                    'return_period' => [
                                        'type' => 'integer',
                                        'example' => 45,
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The updated RMA rule.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 3,
                                    'name' => 'Apparel 45-day returns',
                                    'description' => 'Extended window for clothing.',
                                    'status' => 1,
                                    'returnPeriod' => 45,
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
            uriTemplate: '/rma/rules/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminRmaRuleWriteProvider::class,
            processor: AdminRmaRuleProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Delete an RMA rule',
                description: 'Permission: sales.rma.rules.delete.',
                responses: [
                    '204' => new Model\Response(description: 'The RMA rule was deleted. No body is returned.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.rma.rules.delete.'),
                    '404' => new Model\Response(description: 'The RMA rule was not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminRmaRuleCollectionProvider::class,
            paginationType: 'cursor',
            description: 'The return rules, each setting the return window for the products it matches. Filters: `name` (partial match), `status` (0 or 1). Sortable by id or name. `sort` names a column (or `column-asc`/`column-desc`) and `order` is `asc` or `desc` (default `desc`). Paginate with `first` (page size, default 10, max 50; 0 or less falls back to 10) and `after` (the previous page\'s `endCursor`, sent with the same `first`); `last` and `before` are not supported. Needs the `sales.rma.rules` permission.',
            args: [
                'name' => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
        new Query(
            provider: AdminRmaRuleItemProvider::class,
            description: 'One RMA rule. Needs the `sales.rma.rules` permission.',
        ),
        new Mutation(name: 'create', input: AdminRmaRuleCreateInput::class, processor: AdminRmaRuleProcessor::class),
        new Mutation(name: 'update', input: AdminRmaRuleUpdateInput::class, processor: AdminRmaRuleProcessor::class),
        new Mutation(name: 'delete', input: AdminRmaRuleUpdateInput::class, processor: AdminRmaRuleProcessor::class),
    ],
)]
class AdminRmaRule
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The description.
     */
    public ?string $description = null;

    /**
     * The status.
     */
    public ?int $status = null;

    /**
     * The return period.
     */
    public ?int $return_period = null;

    /**
     * The default.
     */
    public ?int $default = null;

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
