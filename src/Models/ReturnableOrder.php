<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\State\ReturnableOrderProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ReturnableOrder',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/returnable-orders',
            provider: ReturnableOrderProvider::class,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'List the orders a return can still be raised against',
                description: 'The authenticated customer\'s orders that can start a return — the set the storefront shows first in the New Request flow. An order appears while it holds at least one item that was placed with returns allowed, is inside its return window, and has quantity left after everything already returned. Canceled, closed, fraud and pending-payment orders never appear. Pick an order from here, then call /returnable-items?order_id= for the items and their quantity caps.',
                parameters: [
                    new Parameter('increment_id', 'query', 'Filter by order number (partial match).', false, schema: ['type' => 'string']),
                    new Parameter('status', 'query', 'Filter by order status.', false, schema: ['type' => 'string']),
                    new Parameter('sort', 'query', 'Sort column — `created_at` (default), `increment_id` or `grand_total`.', false, schema: ['type' => 'string']),
                    new Parameter('order', 'query', 'Sort direction — `desc` (default) or `asc`.', false, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Response(
                        description: 'Orders eligible for a new return.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 41,
                                        'incrementId' => '41',
                                        'status' => 'completed',
                                        'statusLabel' => 'Completed',
                                        'grandTotal' => 160.0,
                                        'formattedGrandTotal' => '$160.00',
                                        'orderCurrencyCode' => 'USD',
                                        'paymentMethodTitle' => 'Cash On Delivery',
                                        'totalQtyOrdered' => 3,
                                        'totalReturnedQty' => 1,
                                        'returnableQty' => 2,
                                        'createdAt' => '2026-08-30T09:12:44+00:00',
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
            provider: ReturnableOrderProvider::class,
            extraArgs: [
                'incrementId' => ['type' => 'String', 'description' => 'Filter by order number (partial match).'],
                'status' => ['type' => 'String', 'description' => 'Filter by order status.'],
                'sort' => ['type' => 'String', 'description' => 'Sort column — created_at, increment_id or grand_total.'],
                'order' => ['type' => 'String', 'description' => 'Sort direction — desc or asc.'],
            ],
        ),
    ],
)]
class ReturnableOrder
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $increment_id = null;

    public ?string $status = null;

    public ?string $status_label = null;

    public ?float $grand_total = null;

    public ?string $formatted_grand_total = null;

    public ?string $order_currency_code = null;

    public ?string $payment_method_title = null;

    public ?int $total_qty_ordered = null;

    public ?int $total_returned_qty = null;

    public ?int $returnable_qty = null;

    public ?string $created_at = null;
}
