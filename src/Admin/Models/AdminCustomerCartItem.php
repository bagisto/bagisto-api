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
use Webkul\BagistoApi\Admin\State\AdminCustomerCartItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerSidebarItemDeleteProcessor;

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
        new Delete(
            uriTemplate: '/customers/{customerId}/cart-items',
            status: 200,
            input: false,
            read: false,
            deserialize: false,
            validate: false,
            processor: AdminCustomerSidebarItemDeleteProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: "Remove an item from a customer's active cart",
                description: 'Removes an item from the customer\'s own storefront cart (`carts.is_active = 1`) and recollects the cart totals. The item must belong to the customer\'s active cart.',
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
                                'properties' => ['itemId' => ['type' => 'integer', 'example' => 1701]],
                            ],
                            'example' => ['itemId' => 1701],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Cart item removed.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => ['id' => 1701, 'message' => 'Cart item removed successfully.']],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Customer not found, or the cart item does not belong to the customer.'),
                    '422' => new Model\Response(description: '`itemId` missing or unknown.'),
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
        new Mutation(
            name: 'remove',
            input: AdminCustomerSidebarItemRemoveInput::class,
            read: false,
            processor: AdminCustomerSidebarItemDeleteProcessor::class,
            description: 'Remove a cart item of the customer. Pass `id` as /api/admin/customers/{customerId}/cart-items/{itemId}.',
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
