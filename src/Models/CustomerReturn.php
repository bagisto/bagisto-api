<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Dto\CreateCustomerReturnInput;
use Webkul\BagistoApi\Dto\CustomerReturnActionInput;
use Webkul\BagistoApi\State\CustomerReturnProcessor;
use Webkul\BagistoApi\State\CustomerReturnProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerReturn',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/returns',
            provider: CustomerReturnProvider::class,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'List the authenticated customer\'s return (RMA) requests',
                description: 'Returns the customer\'s own RMA requests, newest first. Optional `?status=<id>` filter. Requires a customer Bearer token.',
                responses: [
                    '200' => new Response(
                        description: 'The customer\'s return requests (detail-only fields — canClose/canReopen/isExpired/images — are null on the listing).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 12,
                                        'orderId' => 45,
                                        'orderIncrementId' => '000000045',
                                        'statusId' => 1,
                                        'statusTitle' => 'Pending',
                                        'statusColor' => '#FDB022',
                                        'packageCondition' => 'opened',
                                        'information' => 'Item arrived damaged.',
                                        'canClose' => null,
                                        'canReopen' => null,
                                        'isExpired' => null,

                                        'item' => [
                                            'id' => 30,
                                            'order_item_id' => 78,
                                            'sku' => 'COASTALBREEZEMENSHOODIE',
                                            'name' => "Coastal Breeze Men's Blue Zipper Hoodie",
                                            'quantity' => 1,
                                            'resolution' => 'return',
                                            'reason_id' => 2,
                                            'reason' => 'Damaged product',
                                            'variant_id' => null,
                                        ],

                                        'images' => null,
                                        'messagesCount' => 2,
                                        'createdAt' => '2026-07-20T10:15:30+00:00',
                                        'updatedAt' => '2026-07-20T10:15:30+00:00',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/returns/{id}',
            provider: CustomerReturnProvider::class,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'Get one of the customer\'s return (RMA) requests',
                description: 'Full detail of a single RMA owned by the authenticated customer — the returned item, images, status, and action flags (canClose / canReopen / isExpired). 404 if it is not the customer\'s.',
                responses: [
                    '200' => new Response(
                        description: 'The return request detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'orderId' => 45,
                                    'orderIncrementId' => '000000045',
                                    'statusId' => 1,
                                    'statusTitle' => 'Pending',
                                    'statusColor' => '#FDB022',
                                    'packageCondition' => 'opened',
                                    'information' => 'Item arrived damaged.',
                                    'canClose' => true,
                                    'canReopen' => false,
                                    'isExpired' => false,

                                    'item' => [
                                        'id' => 30,
                                        'order_item_id' => 78,
                                        'sku' => 'COASTALBREEZEMENSHOODIE',
                                        'name' => "Coastal Breeze Men's Blue Zipper Hoodie",
                                        'quantity' => 1,
                                        'resolution' => 'return',
                                        'reason_id' => 2,
                                        'reason' => 'Damaged product',
                                        'variant_id' => null,
                                    ],

                                    'images' => [
                                        [
                                            'id' => 5,
                                            'path' => 'rma/12/damage-front.png',
                                            'url' => 'https://example.com/storage/rma/12/damage-front.png',
                                        ],
                                    ],

                                    'messagesCount' => 2,
                                    'createdAt' => '2026-07-20T10:15:30+00:00',
                                    'updatedAt' => '2026-07-20T10:15:30+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/returns',
            inputFormats: [
                'json' => ['application/json'],
                'multipart' => ['multipart/form-data'],
            ],
            processor: CustomerReturnProcessor::class,
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'Raise a new return (RMA) request',
                description: 'Raises a return for one returnable order item. Attach evidence images in `images[]`.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['order_id', 'order_item_id', 'rma_qty', 'resolution_type', 'rma_reason_id', 'agreement'],

                                'properties' => [
                                    'order_id' => [
                                        'type' => 'integer',
                                        'example' => 12,
                                    ],

                                    'order_item_id' => [
                                        'type' => 'integer',
                                        'example' => 45,
                                    ],

                                    'rma_qty' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                    ],

                                    'resolution_type' => [
                                        'type' => 'string',
                                        'enum' => ['return', 'cancel_items'],
                                        'example' => 'return',
                                    ],

                                    'rma_reason_id' => [
                                        'type' => 'integer',
                                        'example' => 2,
                                    ],

                                    'information' => [
                                        'type' => 'string',
                                        'example' => 'Item arrived damaged.',
                                    ],

                                    'package_condition' => [
                                        'type' => 'string',
                                        'enum' => ['open', 'packed'],
                                        'example' => 'open',
                                    ],

                                    'custom_attributes' => [
                                        'type' => 'string',
                                        'description' => 'JSON object keyed by custom field id.',
                                        'example' => '{"1":"INV-9921"}',
                                    ],

                                    'agreement' => [
                                        'type' => 'boolean',
                                        'example' => true,
                                    ],

                                    'images[]' => [
                                        'type' => 'array',

                                        'items' => [
                                            'type' => 'string',
                                            'format' => 'binary',
                                        ],

                                        'description' => 'Evidence images.',
                                    ],
                                ],
                            ],
                        ],

                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['order_id', 'order_item_id', 'rma_qty', 'resolution_type', 'rma_reason_id', 'agreement'],

                                'properties' => [
                                    'order_id' => [
                                        'type' => 'integer',
                                        'example' => 12,
                                    ],

                                    'order_item_id' => [
                                        'type' => 'integer',
                                        'example' => 45,
                                    ],

                                    'rma_qty' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                    ],

                                    'resolution_type' => [
                                        'type' => 'string',
                                        'enum' => ['return', 'cancel_items'],
                                        'example' => 'return',
                                    ],

                                    'rma_reason_id' => [
                                        'type' => 'integer',
                                        'example' => 2,
                                    ],

                                    'information' => [
                                        'type' => 'string',
                                        'example' => 'Item arrived damaged.',
                                    ],

                                    'package_condition' => [
                                        'type' => 'string',
                                        'enum' => ['open', 'packed'],
                                        'example' => 'open',
                                    ],

                                    'custom_attributes' => [
                                        'type' => 'object',

                                        'example' => [
                                            '1' => 'INV-9921',
                                            '2' => ['morning', 'evening'],
                                        ],
                                    ],

                                    'agreement' => [
                                        'type' => 'boolean',
                                        'example' => true,
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'The created return request.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'orderId' => 45,
                                    'orderIncrementId' => '000000045',
                                    'statusId' => 1,
                                    'statusTitle' => 'Pending',
                                    'statusColor' => '#FDB022',
                                    'packageCondition' => 'opened',
                                    'information' => 'Item arrived damaged.',
                                    'canClose' => true,
                                    'canReopen' => false,
                                    'isExpired' => false,

                                    'item' => [
                                        'id' => 30,
                                        'order_item_id' => 78,
                                        'sku' => 'COASTALBREEZEMENSHOODIE',
                                        'name' => "Coastal Breeze Men's Blue Zipper Hoodie",
                                        'quantity' => 1,
                                        'resolution' => 'return',
                                        'reason_id' => 2,
                                        'reason' => 'Damaged product',
                                        'variant_id' => null,
                                    ],

                                    'images' => [],
                                    'messagesCount' => 0,
                                    'createdAt' => '2026-07-20T10:15:30+00:00',
                                    'updatedAt' => '2026-07-20T10:15:30+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/returns/{id}/cancel',
            processor: CustomerReturnProcessor::class,
            read: false,
            openapi: new Operation(
                requestBody: new RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                tags: ['Customer Return'],
                summary: 'Cancel a return request',
                description: 'Cancels the customer\'s own RMA (unless it is already canceled). Empty body. Returns the updated RMA.',
                responses: [
                    '201' => new Response(
                        description: 'The canceled return request.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'orderId' => 45,
                                    'orderIncrementId' => '000000045',
                                    'statusId' => 4,
                                    'statusTitle' => 'Canceled',
                                    'statusColor' => '#F04438',
                                    'canClose' => false,
                                    'canReopen' => true,
                                    'isExpired' => false,
                                    'messagesCount' => 2,
                                    'updatedAt' => '2026-07-20T11:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/returns/{id}/reopen',
            processor: CustomerReturnProcessor::class,
            read: false,
            openapi: new Operation(
                requestBody: new RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                tags: ['Customer Return'],
                summary: 'Reopen a return request',
                description: 'Reopens a canceled/declined RMA back to pending — only when store settings allow it (otherwise 400). Empty body. Returns the updated RMA.',
                responses: [
                    '201' => new Response(
                        description: 'The reopened return request.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'orderId' => 45,
                                    'orderIncrementId' => '000000045',
                                    'statusId' => 1,
                                    'statusTitle' => 'Pending',
                                    'statusColor' => '#FDB022',
                                    'canClose' => true,
                                    'canReopen' => false,
                                    'isExpired' => false,
                                    'messagesCount' => 2,
                                    'updatedAt' => '2026-07-20T11:05:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/returns/{id}/close',
            processor: CustomerReturnProcessor::class,
            read: false,
            openapi: new Operation(
                requestBody: new RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                tags: ['Customer Return'],
                summary: 'Close (mark solved) a return request',
                description: 'Marks the customer\'s own RMA as solved and adds a conversation note. Empty body. Returns the updated RMA.',
                responses: [
                    '201' => new Response(
                        description: 'The closed (solved) return request.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'orderId' => 45,
                                    'orderIncrementId' => '000000045',
                                    'statusId' => 3,
                                    'statusTitle' => 'Solved',
                                    'statusColor' => '#12B76A',
                                    'canClose' => false,
                                    'canReopen' => false,
                                    'isExpired' => false,
                                    'messagesCount' => 3,
                                    'updatedAt' => '2026-07-20T11:10:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: CustomerReturnProvider::class,
            description: 'One return request on an order of the authenticated customer; any other return is not found. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
        ),
        new Mutation(
            name: 'create',
            input: CreateCustomerReturnInput::class,
            processor: CustomerReturnProcessor::class,
        ),
        new Mutation(
            name: 'cancel',
            input: CustomerReturnActionInput::class,
            processor: CustomerReturnProcessor::class,
        ),
        new Mutation(
            name: 'reopen',
            input: CustomerReturnActionInput::class,
            processor: CustomerReturnProcessor::class,
        ),
        new Mutation(
            name: 'close',
            input: CustomerReturnActionInput::class,
            processor: CustomerReturnProcessor::class,
        ),
        new QueryCollection(
            provider: CustomerReturnProvider::class,
            description: 'Return requests on the authenticated customer\'s orders, newest first; `status` filters by RMA status ID. Paginate forward only: `first` (default 10, between 1 and 50) and `after`. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            paginationType: 'cursor',
            args: [
                'status' => [
                    'type' => 'Int',
                    'description' => 'Filter by RMA status id',
                ],

                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
    ],
)]
class CustomerReturn implements SnakeCaseFieldsResource
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The order id.
     */
    public ?int $order_id = null;

    /**
     * The order increment id.
     */
    public ?string $order_increment_id = null;

    /**
     * The status id.
     */
    public ?int $status_id = null;

    /**
     * The status title.
     */
    public ?string $status_title = null;

    /**
     * The status color.
     */
    public ?string $status_color = null;

    /**
     * The package condition.
     */
    public ?string $package_condition = null;

    /**
     * The information.
     */
    public ?string $information = null;

    /**
     * The can close.
     */
    public ?bool $can_close = null;

    /**
     * The can reopen.
     */
    public ?bool $can_reopen = null;

    /**
     * The is expired.
     */
    public ?bool $is_expired = null;

    /**
     * The order item being returned.
     *
     * @var array<string,mixed>|null
     */
    #[ApiProperty(openapiContext: ['type' => 'object'])]
    public ?array $item = null;

    /**
     * The images attached to the return.
     *
     * @var array<int,array<string,mixed>>|null
     */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $images = null;

    /**
     * The answers to the return custom fields.
     *
     * @var array<int,array<string,mixed>>|null
     */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $custom_attributes = null;

    /**
     * The messages count.
     */
    public ?int $messages_count = null;

    /**
     * The created at.
     */
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    public ?string $updated_at = null;
}
