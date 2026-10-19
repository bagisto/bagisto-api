<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\State\ReturnableItemProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ReturnableItem',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/returnable-items',
            provider: ReturnableItemProvider::class,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'List the return-eligible items of one of the customer\'s orders',
                description: 'Items from the order named by `?order_id=` that are still within their return window and not already fully returned/canceled. Each row carries the trusted quantity caps (`forReturnQuantity`, `forCancelQuantity`, `currentQuantity`) the create endpoint enforces. Requires the order to belong to the authenticated customer.',
                parameters: [
                    new Parameter('order_id', 'query', 'Order id to list returnable items for', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Response(
                        description: 'The return-eligible items of the order, with server-enforced quantity caps.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'orderItemId' => 78,
                                        'productId' => 1,
                                        'sku' => 'COASTALBREEZEMENSHOODIE',
                                        'name' => "Coastal Breeze Men's Blue Zipper Hoodie",
                                        'type' => 'simple',
                                        'urlKey' => 'coastal-breeze-mens-blue-zipper-hoodie',
                                        'price' => 100,
                                        'baseImageUrl' => 'https://example.com/storage/product/1/hoodie.webp',
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
            provider: ReturnableItemProvider::class,
            paginationType: 'cursor',
            args: [
                'orderId' => [
                    'type' => 'Int!',
                    'description' => 'Order id to list returnable items for',
                ],
            ],
        ),
    ],
)]
class ReturnableItem
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
     * The url key.
     */
    public ?string $url_key = null;

    /**
     * The price.
     */
    public ?float $price = null;

    /**
     * The base image url.
     */
    public ?string $base_image_url = null;

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
