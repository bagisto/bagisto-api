<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerRecentOrderItemProvider;

/**
 * Items the customer has ordered recently — right-sidebar "Recent Order Items"
 * panel on the Create-Order screen. Limited to 5 most recent distinct products.
 */
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
                                    'meta' => ['currentPage' => 1, 'perPage' => 1, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
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
            args: ['customerId' => ['type' => 'Int!', 'description' => 'Customer ID']],
        ),
    ]
)]
class AdminCustomerRecentOrderItem
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $product_id = null;

    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?float $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_price = null;

    #[ApiProperty(writable: false)]
    public ?string $product_image = null;

    #[ApiProperty(writable: false)]
    public ?array $additional = null;
}
