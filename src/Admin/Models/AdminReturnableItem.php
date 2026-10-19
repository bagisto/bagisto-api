<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminReturnableItemProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReturnableItem',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/requests/order-items',
            provider: AdminReturnableItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'List the returnable items of an order (RMA create form)',
                description: 'Items of the order named by `?order_id=` that can be put on a return, each with the trusted quantity caps (`forReturnQuantity`, `forCancelQuantity`, `currentQuantity`). No customer scope — admin can open a return for any order.',
                parameters: [
                    new Model\Parameter('order_id', 'query', 'Order id', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The returnable items of the order, with server-enforced quantity caps.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'orderItemId' => 78,
                                        'productId' => 1,
                                        'sku' => 'COASTALBREEZEMENSHOODIE',
                                        'name' => "Coastal Breeze Men's Blue Zipper Hoodie",
                                        'type' => 'simple',
                                        'price' => 100,
                                        'qtyOrdered' => 2,
                                        'currentQuantity' => 2,
                                        'forReturnQuantity' => 2,
                                        'forCancelQuantity' => 0,
                                        'rmaQuantity' => 0,
                                        'rmaReturnPeriod' => 30,
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
            provider: AdminReturnableItemProvider::class,
            paginationType: 'cursor',
            args: [
                'orderId' => [
                    'type' => 'Int!',
                    'description' => 'Order id',
                ],
            ],
        ),
    ],
)]
class AdminReturnableItem
{
    /**
     * The order item id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $order_item_id = null;

    /**
     * The product id.
     */
    public ?int $product_id = null;

    /**
     * The sku.
     */
    public ?string $sku = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The type.
     */
    public ?string $type = null;

    /**
     * The price.
     */
    public ?float $price = null;

    /**
     * The qty ordered.
     */
    public ?int $qty_ordered = null;

    /**
     * The current quantity.
     */
    public ?int $current_quantity = null;

    /**
     * The for return quantity.
     */
    public ?int $for_return_quantity = null;

    /**
     * The for cancel quantity.
     */
    public ?int $for_cancel_quantity = null;

    /**
     * The rma quantity.
     */
    public ?int $rma_quantity = null;

    /**
     * The rma return period.
     */
    public ?int $rma_return_period = null;
}
