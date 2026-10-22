<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerRecentOrderItemProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerRecentOrderItem',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/customers/{customerId}/recent-order-items',
            provider: AdminCustomerRecentOrderItemProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: "Get a customer's recent order items",
                description: 'Top-level (`parent_id IS NULL`) items from the customer\'s most-recent orders, distinct by product, limited to 5.',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Up to 5 recent items.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 2694, 'productId' => 2358, 'sku' => 'test65',
                                            'type' => 'simple', 'name' => 'Classic Watch Hand',
                                            'price' => 4000, 'formattedPrice' => '$4,000.00',
                                            'productImage' => 'http://localhost:8000/storage/product/2358/example.webp',
                                            'additional' => ['quantity' => 1],
                                        ],
                                    ],

                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 1,
                                        'lastPage' => 1,
                                        'total' => 1,
                                        'from' => 1,
                                        'to' => 1,
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
            provider: AdminCustomerRecentOrderItemProvider::class,
            paginationType: 'cursor',
            description: 'Up to 5 top-level items from the most recent orders of customer `customerId`, one per product, newest first, in one page (no pagination arguments). Part of the Create-Order flow, so it needs the `sales.orders.create` permission; an unknown customer is an error.',

            args: ['customerId' => [
                'type' => 'Int!',
                'description' => 'Customer ID',
            ]],
        ),
    ]
)]
class AdminCustomerRecentOrderItem
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The product id.
     */
    #[ApiProperty(writable: false)]
    public ?int $product_id = null;

    /**
     * The sku.
     */
    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    /**
     * The type.
     */
    #[ApiProperty(writable: false)]
    public ?string $type = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * The price.
     */
    #[ApiProperty(writable: false)]
    public ?float $price = null;

    /**
     * The formatted price.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_price = null;

    /**
     * The product image.
     */
    #[ApiProperty(writable: false)]
    public ?string $product_image = null;

    /**
     * The additional.
     */
    #[ApiProperty(writable: false)]
    public ?array $additional = null;
}
