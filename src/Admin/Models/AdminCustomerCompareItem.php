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
use Webkul\BagistoApi\Admin\State\AdminCustomerCompareItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerSidebarItemDeleteProcessor;

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
                                    'meta' => ['currentPage' => 1, 'perPage' => 1, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/customers/{customerId}/compare-items',
            status: 200,
            input: false,
            read: false,
            deserialize: false,
            validate: false,
            processor: AdminCustomerSidebarItemDeleteProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Customers'],
                summary: "Remove an item from a customer's compare list",
                description: 'Removes a compare item of the customer. The item must belong to the customer.',
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
                                'properties' => ['itemId' => ['type' => 'integer', 'example' => 12]],
                            ],
                            'example' => ['itemId' => 12],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Compare item removed.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => ['id' => 12, 'message' => 'Compare item removed successfully.']],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Customer not found, or the compare item does not belong to the customer.'),
                    '422' => new Model\Response(description: '`itemId` missing or unknown.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCustomerCompareItemProvider::class,
            paginationType: 'cursor',
            args: ['customerId' => ['type' => 'Int!', 'description' => 'Customer ID']],
        ),
        new Mutation(
            name: 'remove',
            input: AdminCustomerSidebarItemRemoveInput::class,
            read: false,
            processor: AdminCustomerSidebarItemDeleteProcessor::class,
            description: 'Remove a compare item of the customer. Pass `id` as /api/admin/customers/{customerId}/compare-items/{itemId}.',
        ),
    ]
)]
class AdminCustomerCompareItem
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
}
