<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerCompareItemProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerCompareItem',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/customers/{customerId}/compare-items',
            provider: AdminCustomerCompareItemProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: "Get a customer's compare list",
                description: 'The products in the customer\'s compare list. An unknown customer is a 404.',
                parameters: [
                    new Model\Parameter('customerId', 'path', 'Customer ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Compare items for the customer.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 12, 'productId' => 2358, 'sku' => 'test65',
                                            'name' => 'Classic Watch Hand', 'price' => 4000,
                                            'formattedPrice' => '$4,000.00',
                                            'productImage' => 'http://localhost:8000/storage/product/2358/example.webp',
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
            provider: AdminCustomerCompareItemProvider::class,
            paginationType: 'cursor',
            description: 'Every product in the compare list of customer `customerId`, all in one page (no pagination arguments). It needs the `customers.customers` permission, as the customer page in the admin panel does; an unknown customer is an error.',

            args: ['customerId' => [
                'type' => 'Int!',
                'description' => 'Customer ID',
            ]],
        ),
    ]
)]
class AdminCustomerCompareItem
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
     * The product type.
     */
    #[ApiProperty(writable: false)]
    public ?string $type = null;

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
