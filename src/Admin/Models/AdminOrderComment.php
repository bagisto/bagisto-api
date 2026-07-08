<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminOrderCommentCreateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminOrderCommentCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminOrderCommentProvider;

/**
 * Admin order comments.
 *
 * REST  : POST   /api/admin/orders/{id}/comments  (create)
 *         GET    /api/admin/orders/{id}/comments  (cursor list, newest first)
 * GraphQL: createAdminOrderComment mutation + adminOrderComments query collection.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminOrderComment',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/orders/{orderId}/comments',
            provider: AdminOrderCommentProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'List comments on an order',
                description: 'Returns all comments newest-first in a `{ data, meta }` envelope.',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/orders/{orderId}/comments',
            input: AdminOrderCommentCreateInput::class,
            processor: AdminOrderCommentCreateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Add a comment to an order',
                description: 'Persists an order comment. When `customerNotified=true` core listeners send the customer email.',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => [
                                'comment' => 'Customer called to confirm shipping address.',
                                'customerNotified' => true,
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminOrderCommentProvider::class,
            paginationType: 'cursor',
            description: 'Cursor-paginated list of an order\'s comments (newest first). Pass the order id as `orderId`.',
            extraArgs: ['orderId' => ['type' => 'Int!']],
        ),
        new Mutation(
            name: 'create',
            input: AdminOrderCommentCreateInput::class,
            processor: AdminOrderCommentCreateProcessor::class,
            description: 'Add a comment to an order.',
        ),
    ],
)]
class AdminOrderComment
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $order_id = null;

    #[ApiProperty(writable: false)]
    public ?string $comment = null;

    #[ApiProperty(writable: false)]
    public ?bool $customer_notified = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
