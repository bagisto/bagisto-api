<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCreateReturnInput;
use Webkul\BagistoApi\Admin\Dto\AdminReturnActionInput;
use Webkul\BagistoApi\Admin\Dto\AdminReturnUpdateStatusInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminReturnCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminReturnItemProvider;
use Webkul\BagistoApi\Admin\State\AdminReturnProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReturn',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/requests',
            provider: AdminReturnCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'List RMA (return) requests',
                description: 'Admin listing of every return request (RMADataGrid parity). Filters: id, order_id, status (title), customer_name, created_at range. Sort: id (default desc), order_id, created_at.',
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated return requests. Detail-only fields (item/images/availableStatuses/information/messagesCount/canReopen) are null on the listing.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 12,
                                            'orderId' => 45,
                                            'orderIncrementId' => '000000045',
                                            'orderStatus' => 'processing',
                                            'customerName' => 'Jane Doe',
                                            'customerEmail' => 'jane@example.com',
                                            'isGuest' => 0,
                                            'statusId' => 1,
                                            'statusTitle' => 'Pending',
                                            'statusColor' => '#FDB022',
                                            'packageCondition' => null,
                                            'information' => null,
                                            'canReopen' => null,
                                            'item' => null,
                                            'images' => null,
                                            'availableStatuses' => null,
                                            'messagesCount' => null,
                                            'createdAt' => '2026-07-20T10:15:30+00:00',
                                            'updatedAt' => '2026-07-20T10:15:30+00:00',
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
            uriTemplate: '/rma/requests/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminReturnItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Get one RMA request',
                description: 'Full detail of a single return request — the returned item, images, status, the customer/order context, whether it can be reopened, and the list of status transitions the admin may set next. Each image `url` points at `GET /api/admin/rma/requests/images/{id}`, which needs the admin token.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The return request detail. availableStatuses lists the status transitions the admin may set next.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'orderId' => 45,
                                    'orderIncrementId' => '000000045',
                                    'orderStatus' => 'processing',
                                    'customerName' => 'Jane Doe',
                                    'customerEmail' => 'jane@example.com',
                                    'isGuest' => 0,
                                    'statusId' => 1,
                                    'statusTitle' => 'Pending',
                                    'statusColor' => '#FDB022',
                                    'packageCondition' => 'opened',
                                    'information' => 'Customer reported a defect.',
                                    'canReopen' => false,

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
                                            'path' => 'rmas/12/images/damage-front.png',
                                            'url' => 'https://example.com/api/admin/rma/requests/images/5',
                                        ],
                                    ],

                                    'availableStatuses' => [
                                        [
                                            'id' => 2,
                                            'title' => 'Accept',
                                        ], [
                                            'id' => 3,
                                            'title' => 'Declined',
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
            uriTemplate: '/rma/requests',
            inputFormats: [
                'json' => ['application/json'],
                'multipart' => ['multipart/form-data'],
            ],
            processor: AdminReturnProcessor::class,
            read: false,
            deserialize: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Create an RMA request for a customer order',
                description: 'Admin-side return creation for any order. `rmaQty` is capped server-side by the item\'s returnable/cancelable quantity. Optional image files via multipart `images[]`, or a single `images` file (REST only). Returns the created RMA. Permission: sales.rma.requests.create.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['order_id', 'order_item_id', 'rma_qty', 'resolution_type', 'rma_reason_id'],

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
                                        'example' => 'Customer reported a defect.',
                                    ],

                                    'package_condition' => [
                                        'type' => 'string',
                                        'example' => 'opened',
                                    ],
                                ],
                            ],
                        ],

                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['order_id', 'order_item_id', 'rma_qty', 'resolution_type', 'rma_reason_id'],

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
                                        'example' => 'Customer reported a defect.',
                                    ],

                                    'package_condition' => [
                                        'type' => 'string',
                                        'example' => 'opened',
                                    ],

                                    'images[]' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                            'format' => 'binary',
                                        ],
                                        'description' => 'Photos of the returned item.',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'The created return request.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'orderId' => 45,
                                    'orderIncrementId' => '000000045',
                                    'orderStatus' => 'processing',
                                    'customerName' => 'Jane Doe',
                                    'customerEmail' => 'jane@example.com',
                                    'isGuest' => 0,
                                    'statusId' => 1,
                                    'statusTitle' => 'Pending',
                                    'statusColor' => '#FDB022',
                                    'packageCondition' => 'opened',
                                    'information' => 'Customer reported a defect.',
                                    'canReopen' => false,

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

                                    'availableStatuses' => [
                                        [
                                            'id' => 2,
                                            'title' => 'Accept',
                                        ], [
                                            'id' => 3,
                                            'title' => 'Declined',
                                        ],
                                    ],

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
            uriTemplate: '/rma/requests/{id}/update-status',
            processor: AdminReturnProcessor::class,
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Update the status of an RMA request',
                description: 'Sets the RMA status. Status 5 (received package) creates a refund for the returned item (send `shipping` to include shipping); status 8 (item canceled) cancels the order item and restores its inventory; any other status just updates the status. Adds a status note and notifies the customer. Returns the updated RMA. Permission: sales.rma.requests.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['rma_status_id'],

                                'properties' => [
                                    'rma_status_id' => [
                                        'type' => 'integer',
                                        'example' => 2,
                                    ],

                                    'shipping' => [
                                        'type' => 'number',
                                        'example' => 0,
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'The updated return request after the status change.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'orderId' => 45,
                                    'orderIncrementId' => '000000045',
                                    'statusId' => 2,
                                    'statusTitle' => 'Accept',
                                    'statusColor' => '#12B76A',
                                    'canReopen' => false,

                                    'availableStatuses' => [
                                        [
                                            'id' => 5,
                                            'title' => 'Received package',
                                        ], [
                                            'id' => 4,
                                            'title' => 'Dispatched package',
                                        ],
                                    ],

                                    'messagesCount' => 3,
                                    'updatedAt' => '2026-07-20T11:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/rma/requests/{id}/reopen',
            processor: AdminReturnProcessor::class,
            read: false,
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                tags: ['Admin Sales: RMA'],
                summary: 'Reopen an RMA request',
                description: 'Reopens a declined/canceled RMA back to pending when store settings allow it (otherwise 422). Empty body. Returns the updated RMA. Permission: sales.rma.requests.',
                responses: [
                    '201' => new Model\Response(
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
                                    'canReopen' => false,

                                    'availableStatuses' => [
                                        [
                                            'id' => 2,
                                            'title' => 'Accept',
                                        ], [
                                            'id' => 3,
                                            'title' => 'Declined',
                                        ],
                                    ],

                                    'messagesCount' => 3,
                                    'updatedAt' => '2026-07-20T11:20:00+00:00',
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
            provider: AdminReturnCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Every return (RMA) request, as in the admin RMA grid. Filters: `id`, `order_id`, `status` (partial match on the status title) and `customer_name` (partial match). Sortable by id, order_id or created_at. `sort` names a column (or `column-asc`/`column-desc`) and `order` is `asc` or `desc` (default `desc`). Paginate with `first` (page size, default 10, max 50; 0 or less falls back to 10) and `after` (the previous page\'s `endCursor`, sent with the same `first`); `last` and `before` are not supported. Needs the `sales.rma.requests` permission.',
            args: [
                'id' => ['type' => 'Int'],
                'order_id' => ['type' => 'Int'],
                'status' => ['type' => 'String'],
                'customer_name' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
        new Query(
            provider: AdminReturnItemProvider::class,
            description: 'One return request with the returned item, images, status, order and customer context, whether it can be reopened and the statuses the admin may set next. Needs the `sales.rma.requests` permission.',
        ),
        new Mutation(
            name: 'create',
            input: AdminCreateReturnInput::class,
            processor: AdminReturnProcessor::class,
        ),
        new Mutation(
            name: 'updateStatus',
            input: AdminReturnUpdateStatusInput::class,
            processor: AdminReturnProcessor::class,
        ),
        new Mutation(
            name: 'reopen',
            input: AdminReturnActionInput::class,
            processor: AdminReturnProcessor::class,
        ),
    ],
)]
class AdminReturn
{
    use AcceptsCamelCaseWrites;

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
     * The order status.
     */
    public ?string $order_status = null;

    /**
     * The customer name.
     */
    public ?string $customer_name = null;

    /**
     * The customer email.
     */
    public ?string $customer_email = null;

    /**
     * The is guest.
     */
    public ?int $is_guest = null;

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
     * The can reopen.
     */
    public ?bool $can_reopen = null;

    /**
     * The returned item.
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
     * The status transitions the admin may set next.
     *
     * @var array<int,array<string,mixed>>|null
     */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $available_statuses = null;

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
