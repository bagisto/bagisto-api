<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminPlaceOrderInput;
use Webkul\BagistoApi\Admin\State\AdminPlaceOrderProcessor;

/**
 * Finalises a fully-prepared admin draft cart into a real order — the Place
 * Order button on `admin.sales.orders.create`.
 *
 * REST    : POST /api/admin/orders/place/{cartId}
 * GraphQL : createAdminPlaceOrder(input: { cartId: Int! })
 *
 * Mirrors `Webkul\Admin\Http\Controllers\Sales\OrderController::store`:
 *   Cart::setCart -> Cart::collectTotals -> validateOrder ->
 *   payment in ['cashondelivery','moneytransfer'] (monolith restriction) ->
 *   OrderResource serialise -> OrderRepository::create -> Cart::removeCart.
 *
 * Returns the created order id + increment id. Cart, addresses, shipping and
 * payment must already be set on the draft cart before calling — each missing
 * step returns HTTP 409 with a precise message rather than relying on the
 * core's generic 500.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminPlaceOrder',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/orders/place/{cartId}',
            input: false,
            processor: AdminPlaceOrderProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Place an order from a draft cart',
                description: "Finalises the given draft cart into an order. Mirrors the monolith `admin.sales.orders.store` flow. Returns 409 if a prerequisite (addresses / shipping / payment method) is missing, and 422 if the payment method is not in `['cashondelivery', 'moneytransfer']`.",
                parameters: [
                    new Model\Parameter('cartId', 'path', 'Draft Cart ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(required: false),
                responses: [
                    '201' => new Model\Response(
                        description: 'Order placed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'orderId'     => 1284,
                                    'incrementId' => '1000001284',
                                    'customerId'  => 7,
                                    'grandTotal'  => 149.99,
                                    'success'     => true,
                                    'message'     => 'Order placed successfully.',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminPlaceOrderInput::class,
            output: self::class,
            processor: AdminPlaceOrderProcessor::class,
            description: 'Finalise a draft cart into an order.',
        ),
    ]
)]
class AdminPlaceOrder
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $orderId = null;

    #[ApiProperty(writable: false)]
    public ?string $incrementId = null;

    #[ApiProperty(writable: false)]
    public ?int $customerId = null;

    #[ApiProperty(writable: false)]
    public ?float $grandTotal = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
