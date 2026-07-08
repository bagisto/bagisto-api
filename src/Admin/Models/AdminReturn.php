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
            ),
        ),
        new Get(
            uriTemplate: '/rma/requests/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminReturnItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Get one RMA request',
                description: 'Full detail of a single return request — the returned item, images, status, the customer/order context, whether it can be reopened, and the list of status transitions the admin may set next.',
            ),
        ),
        new Post(
            uriTemplate: '/rma/requests',
            processor: AdminReturnProcessor::class,
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Create an RMA request for a customer order',
                description: 'Admin-side return creation for any order. `rmaQty` is capped server-side by the item\'s returnable/cancelable quantity. Optional image files via multipart `images[]` (REST only). Returns the created RMA. Permission: sales.rma.requests.create.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'     => 'object',
                                'required' => ['order_id', 'order_item_id', 'rma_qty', 'resolution_type', 'rma_reason_id'],
                                'properties' => [
                                    'order_id'          => ['type' => 'integer', 'example' => 12],
                                    'order_item_id'     => ['type' => 'integer', 'example' => 45],
                                    'rma_qty'           => ['type' => 'integer', 'example' => 1],
                                    'resolution_type'   => ['type' => 'string', 'enum' => ['return', 'cancel_items'], 'example' => 'return'],
                                    'rma_reason_id'     => ['type' => 'integer', 'example' => 2],
                                    'information'       => ['type' => 'string', 'example' => 'Customer reported a defect.'],
                                    'package_condition' => ['type' => 'string', 'example' => 'opened'],
                                ],
                            ],
                        ],
                    ]),
                ),
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
                                'type'     => 'object',
                                'required' => ['rma_status_id'],
                                'properties' => [
                                    'rma_status_id' => ['type' => 'integer', 'example' => 2],
                                    'shipping'      => ['type' => 'number', 'example' => 0],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Post(
            uriTemplate: '/rma/requests/{id}/reopen',
            processor: AdminReturnProcessor::class,
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'Reopen an RMA request',
                description: 'Reopens a declined/canceled RMA back to pending when store settings allow it (otherwise 422). Empty body. Returns the updated RMA. Permission: sales.rma.requests.',
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminReturnCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'id'            => ['type' => 'Int'],
                'order_id'      => ['type' => 'Int'],
                'status'        => ['type' => 'String'],
                'customer_name' => ['type' => 'String'],
                'sort'          => ['type' => 'String'],
                'order'         => ['type' => 'String'],
                'first'         => ['type' => 'Int'],
                'after'         => ['type' => 'String'],
            ],
        ),
        new Query(provider: AdminReturnItemProvider::class),
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

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $order_id = null;

    public ?string $order_increment_id = null;

    public ?string $order_status = null;

    public ?string $customer_name = null;

    public ?string $customer_email = null;

    public ?int $is_guest = null;

    public ?int $status_id = null;

    public ?string $status_title = null;

    public ?string $status_color = null;

    public ?string $package_condition = null;

    public ?string $information = null;

    public ?bool $can_reopen = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(openapiContext: ['type' => 'object'])]
    public ?array $item = null;

    /** @var array<int,array<string,mixed>>|null */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $images = null;

    /** @var array<int,array<string,mixed>>|null */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $available_statuses = null;

    public ?int $messages_count = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
