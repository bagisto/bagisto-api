<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerCartItemProvider;

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
            provider: AdminCustomerCartItemProvider::class,
            paginationType: 'cursor',
            description: 'Top-level items in the active cart of customer `customerId`, all in one page (no pagination arguments); empty when the customer has no active cart. It needs the `customers.customers` permission, as the customer page in the admin panel does; an unknown customer is an error.',
            args: [
                'customerId' => [
                    'type' => 'Int!',
                    'description' => 'Customer ID',
                ],
            ],
        ),
    ]
)]
class AdminCustomerCartItem
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
     * The quantity.
     */
    #[ApiProperty(writable: false)]
    public ?int $quantity = null;

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
     * The total.
     */
    #[ApiProperty(writable: false)]
    public ?float $total = null;

    /**
     * The formatted total.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_total = null;

    /**
     * The additional.
     */
    #[ApiProperty(writable: false)]
    public ?array $additional = null;

    /**
     * The id of the cart the item is in.
     */
    #[ApiProperty(writable: false)]
    public ?int $cart_id = null;

    /**
     * The unit price including tax.
     */
    #[ApiProperty(writable: false)]
    public ?float $price_incl_tax = null;

    /**
     * The unit price including tax, formatted.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_price_incl_tax = null;

    /**
     * The row total including tax.
     */
    #[ApiProperty(writable: false)]
    public ?float $total_incl_tax = null;

    /**
     * The row total including tax, formatted.
     */
    #[ApiProperty(writable: false)]
    public ?string $formatted_total_incl_tax = null;

    /**
     * The chosen options, as the admin cart panel lists them.
     */
    #[ApiProperty(writable: false)]
    public ?array $options = null;

    /**
     * The URL of the product's first image.
     */
    #[ApiProperty(writable: false)]
    public ?string $product_image = null;

    /**
     * Whether the product can be bought.
     */
    #[ApiProperty(writable: false)]
    public ?bool $is_saleable = null;

    /**
     * The quantity available for sale, or null where stock does not apply.
     */
    #[ApiProperty(writable: false)]
    public ?int $qty_available = null;

    /**
     * Whether the product needs options chosen before it can be added to a cart.
     */
    #[ApiProperty(writable: false)]
    public ?bool $is_options_required = null;
}
