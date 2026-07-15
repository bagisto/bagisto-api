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
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminReturnableItemProvider::class,
            paginationType: 'cursor',
            args: [
                'orderId' => ['type' => 'Int!', 'description' => 'Order id'],
            ],
        ),
    ],
)]
class AdminReturnableItem
{
    #[ApiProperty(identifier: true)]
    public ?int $order_item_id = null;

    public ?int $product_id = null;

    public ?string $sku = null;

    public ?string $name = null;

    public ?string $type = null;

    public ?float $price = null;

    public ?int $qty_ordered = null;

    public ?int $current_quantity = null;

    public ?int $for_return_quantity = null;

    public ?int $for_cancel_quantity = null;

    public ?int $rma_quantity = null;

    public ?int $rma_return_period = null;
}
