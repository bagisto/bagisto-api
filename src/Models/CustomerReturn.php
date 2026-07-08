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
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Return'],
                summary: 'List the authenticated customer\'s return (RMA) requests',
                description: 'Returns the customer\'s own RMA requests, newest first. Optional `?status=<id>` filter. Requires a customer Bearer token.',
            ),
        ),
        new Get(
            uriTemplate: '/returns/{id}',
            provider: CustomerReturnProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Return'],
                summary: 'Get one of the customer\'s return (RMA) requests',
                description: 'Full detail of a single RMA owned by the authenticated customer — the returned item, images, status, and action flags (canClose / canReopen / isExpired). 404 if it is not the customer\'s.',
            ),
        ),
        new Post(
            uriTemplate: '/returns',
            processor: CustomerReturnProcessor::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Return'],
                summary: 'Raise a new return (RMA) request',
                description: 'Creates a return for one item of one of the customer\'s orders. The item must be return-eligible (see /returnable-items/{orderId}); `rmaQty` is capped server-side by the returnable quantity. Send `agreement=true`. Optional image files can be attached via multipart `images[]` (REST only). Returns the created RMA.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => [
                                'type'     => 'object',
                                'required' => ['order_id', 'order_item_id', 'rma_qty', 'resolution_type', 'rma_reason_id', 'agreement'],
                                'properties' => [
                                    'order_id'          => ['type' => 'integer', 'example' => 12],
                                    'order_item_id'     => ['type' => 'integer', 'example' => 45],
                                    'rma_qty'           => ['type' => 'integer', 'example' => 1],
                                    'resolution_type'   => ['type' => 'string', 'enum' => ['return', 'cancel_items'], 'example' => 'return'],
                                    'rma_reason_id'     => ['type' => 'integer', 'example' => 2],
                                    'information'       => ['type' => 'string', 'example' => 'Item arrived damaged.'],
                                    'package_condition' => ['type' => 'string', 'example' => 'opened'],
                                    'agreement'         => ['type' => 'boolean', 'example' => true],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Post(
            uriTemplate: '/returns/{id}/cancel',
            processor: CustomerReturnProcessor::class,
            read: false,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Return'],
                summary: 'Cancel a return request',
                description: 'Cancels the customer\'s own RMA (unless it is already canceled). Empty body. Returns the updated RMA.',
            ),
        ),
        new Post(
            uriTemplate: '/returns/{id}/reopen',
            processor: CustomerReturnProcessor::class,
            read: false,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Return'],
                summary: 'Reopen a return request',
                description: 'Reopens a canceled/declined RMA back to pending — only when store settings allow it (otherwise 400). Empty body. Returns the updated RMA.',
            ),
        ),
        new Post(
            uriTemplate: '/returns/{id}/close',
            processor: CustomerReturnProcessor::class,
            read: false,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Return'],
                summary: 'Close (mark solved) a return request',
                description: 'Marks the customer\'s own RMA as solved and adds a conversation note. Empty body. Returns the updated RMA.',
            ),
        ),
    ],
    graphQlOperations: [
        new Query(provider: CustomerReturnProvider::class),
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
            paginationType: 'cursor',
            args: [
                'status' => ['type' => 'Int', 'description' => 'Filter by RMA status id'],
                'first'  => ['type' => 'Int'],
                'after'  => ['type' => 'String'],
            ],
        ),
    ],
)]
class CustomerReturn implements SnakeCaseFieldsResource
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $order_id = null;

    public ?string $order_increment_id = null;

    public ?int $status_id = null;

    public ?string $status_title = null;

    public ?string $status_color = null;

    public ?string $package_condition = null;

    public ?string $information = null;

    public ?bool $can_close = null;

    public ?bool $can_reopen = null;

    public ?bool $is_expired = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(openapiContext: ['type' => 'object'])]
    public ?array $item = null;

    /** @var array<int,array<string,mixed>>|null */
    #[ApiProperty(openapiContext: ['type' => 'array'])]
    public ?array $images = null;

    public ?int $messages_count = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
