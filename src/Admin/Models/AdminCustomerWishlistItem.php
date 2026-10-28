<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerSidebarItemRemoveInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerSidebarItemDeleteProcessor;
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
                                            'id' => 88, 'productId' => 2358, 'sku' => 'test65',
                                            'name' => 'Classic Watch Hand', 'price' => 4000,
                                            'formattedPrice' => '$4,000.00',
                                            'productImage' => 'http://localhost:8000/storage/product/2358/example.webp',
                                            'additional' => null,
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
        new Delete(
            uriTemplate: '/customers/{customerId}/wishlist-items',
            status: 200,
            input: false,
            read: false,
            deserialize: false,
            validate: false,
            processor: AdminCustomerSidebarItemDeleteProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: "Remove an item from a customer's wishlist",
                description: 'Removes a wishlist item of the customer. The item must belong to the customer.',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['itemId'],
                                'properties' => ['itemId' => ['type' => 'integer', 'example' => 88]],
                            ],
                            'example' => ['itemId' => 88],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Wishlist item removed.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => ['id' => 88, 'message' => 'Wishlist item removed successfully.']],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Customer not found, or the wishlist item does not belong to the customer.'),
                    '422' => new Model\Response(description: '`itemId` missing or unknown.'),
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
        new Mutation(
            name: 'remove',
            input: AdminCustomerSidebarItemRemoveInput::class,
            read: false,
            processor: AdminCustomerSidebarItemDeleteProcessor::class,
            description: 'Remove a wishlist item of the customer. Pass `id` as /api/admin/customers/{customerId}/wishlist-items/{itemId}.',
        ),
    ]
)]
class AdminCustomerWishlistItem
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $product_id = null;

    #[ApiProperty(writable: false)]
    public ?string $sku = null;

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
