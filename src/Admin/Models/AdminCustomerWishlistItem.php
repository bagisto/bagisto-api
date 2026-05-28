<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminCustomerWishlistItemProvider;

/**
 * Customer's wishlist — the right-sidebar "Wishlist Items" panel on the
 * Create-Order screen. Read-only.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerWishlistItem',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/customers/{customerId}/wishlist-items',
            provider: AdminCustomerWishlistItemProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: "Get a customer's wishlist",
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Wishlist items for the customer.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'             => 88, 'productId' => 2358, 'sku' => 'test65',
                                            'name'           => 'Classic Watch Hand', 'price' => 4000,
                                            'formattedPrice' => '$4,000.00',
                                            'productImage'   => 'http://localhost:8000/storage/product/2358/example.webp',
                                            'additional'     => null,
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
            provider: AdminCustomerWishlistItemProvider::class,
            paginationType: 'cursor',
            args: ['customerId' => ['type' => 'Int!', 'description' => 'Customer ID']],
        ),
    ]
)]
class AdminCustomerWishlistItem
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $productId = null;

    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?float $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedPrice = null;

    #[ApiProperty(writable: false)]
    public ?string $productImage = null;

    #[ApiProperty(writable: false)]
    public ?array $additional = null;
}
