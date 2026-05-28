<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminReorderInput;
use Webkul\BagistoApi\Admin\State\AdminReorderProcessor;

/**
 * Admin Reorder — builds a fresh admin draft cart from a previous order's
 * items, ready for the admin to finalise on the customer's behalf.
 *
 * REST  : POST /api/admin/orders/{id}/reorder       (id from URL, no body)
 * GraphQL: createAdminReorder(input: { id: ... })  (id from input)
 *
 * Mirrors the monolith admin Reorder button: `Cart::createCart` for the order's
 * customer with `is_active = false`, then re-adds each item via
 * `Cart::addProduct($item->product, $item->additional)`. Guest orders can't be
 * reordered (no customer to attach) — see `Order::canReorder()`.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReorder',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/orders/{id}/reorder',
            input: false,
            processor: AdminReorderProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Orders'],
                summary: 'Reorder an order',
                description: "Builds a fresh admin draft cart from the given order's items and returns the new cart ID. The admin can then finalise the order in `admin.sales.orders.create`. Returns `success: false` if the order can't be reordered (guest order or any item is no longer saleable).",
                responses: [
                    '201' => new Model\Response(
                        description: 'Reorder accepted; a new draft cart was created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Reorder successful. A new draft cart has been created.',
                                    'cartId'  => 314,
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
            input: AdminReorderInput::class,
            output: self::class,
            processor: AdminReorderProcessor::class,
            description: "Build a fresh admin draft cart from a previous order's items.",
        ),
    ]
)]
class AdminReorder
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;

    #[ApiProperty(writable: false)]
    public ?int $cartId = null;
}
