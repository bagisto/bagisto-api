<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerCartItemProvider;

/**
 * Items in the customer's OWN active storefront cart — the right-sidebar
 * "Cart Items" panel on the Create-Order screen.
 *
 * Reads `carts.is_active = 1` for the given customer; not the admin draft
 * cart. Returns only top-level items (`cart_items.parent_id IS NULL`).
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerCartItem',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/customers/{customerId}/cart-items',
            provider: AdminCustomerCartItemProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: "Get items in a customer's active cart",
                description: "Items the customer has in their own storefront cart (`carts.is_active = 1`). Used by the Create-Order screen's right sidebar so the admin can pull items into the draft cart.",
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Active cart items (empty data array if the customer has no active cart).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 1701, 'productId' => 2358, 'sku' => 'test65',
                                            'type' => 'simple', 'name' => 'Classic Watch Hand',
                                            'quantity' => 1, 'price' => 4000, 'formattedPrice' => '$4,000.00',
                                            'total' => 4000, 'formattedTotal' => '$4,000.00',
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
            provider: AdminCustomerCartItemProvider::class,
            paginationType: 'cursor',
            args: [
                'customerId' => ['type' => 'Int!', 'description' => 'Customer ID'],
            ],
        ),
    ]
)]
class AdminCustomerCartItem
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
    public ?int $quantity = null;

    #[ApiProperty(writable: false)]
    public ?float $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_price = null;

    #[ApiProperty(writable: false)]
    public ?float $total = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_total = null;

    #[ApiProperty(writable: false)]
    public ?array $additional = null;
}
