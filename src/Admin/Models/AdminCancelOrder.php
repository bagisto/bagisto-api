<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCancelOrderInput;
use Webkul\BagistoApi\Admin\State\AdminCancelOrderProcessor;

/**
 * Admin Cancel Order — POST /api/admin/orders/{id}/cancel
 * (GraphQL: createAdminCancelOrder).
 *
 * The mutation returns the updated `OrderDetail` shape directly so the client
 * can refresh the order-view screen without a follow-up GET.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCancelOrder',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/orders/{id}/cancel',
            output: OrderDetail::class,
            processor: AdminCancelOrderProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Cancel an order',
                description: 'Cancels every cancellable item on the order and returns the updated `OrderDetail`. Gated by the same conditions as the admin UI (open status, qty_to_cancel, `sales.orders.cancel` permission).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'The updated order detail.'),
                    '404' => new Model\Response(description: 'Order not found.'),
                    '422' => new Model\Response(description: 'Order is closed / fraud / has nothing to cancel, or admin lacks `sales.orders.cancel`.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminCancelOrderInput::class,
            processor: AdminCancelOrderProcessor::class,
            description: 'Cancel an order and return the updated order summary.',
        ),
    ],
)]
class AdminCancelOrder
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $orderId = null;

    #[ApiProperty(writable: false)]
    public ?string $incrementId = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?string $statusLabel = null;

    #[ApiProperty(writable: false)]
    public ?float $grandTotal = null;

    #[ApiProperty(writable: false)]
    public ?float $baseGrandTotal = null;

    #[ApiProperty(writable: false)]
    public ?int $totalQtyOrdered = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
