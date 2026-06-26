<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductCustomerGroupPriceCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductCustomerGroupPriceUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCustomerGroupPriceProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCustomerGroupPriceProvider;

/**
 * Admin sub-resource for product customer-group (tier) prices.
 *
 * REST:
 *   GET    /api/admin/catalog/products/{productId}/customer-group-prices
 *   POST   /api/admin/catalog/products/{productId}/customer-group-prices
 *   PUT    /api/admin/catalog/products/{productId}/customer-group-prices/{id}
 *   DELETE /api/admin/catalog/products/{productId}/customer-group-prices/{id}
 *
 * GraphQL:
 *   adminCatalogProductCustomerGroupPrices(productId:)
 *   createAdminCatalogProductCustomerGroupPrice
 *   updateAdminCatalogProductCustomerGroupPrice
 *   deleteAdminCatalogProductCustomerGroupPrice
 *
 * NOTE: this is the **fresh** sub-resource that replaces the legacy admin
 * operations on `src/Models/ProductCustomerGroupPrice.php`. The legacy file
 * still exists for parity until removed (see CLAUDE.md "Legacy admin
 * endpoints — to be removed").
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProductCustomerGroupPrice',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/catalog/products/{productId}/customer-group-prices',
            provider: AdminCatalogProductCustomerGroupPriceProvider::class,
            paginationEnabled: false,
            requirements: ['productId' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'List customer-group (tier) prices for a product',
                description: 'Returns every customer-group price row attached to the product, wrapped in the standard admin `{ data, meta }` envelope.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Customer-group prices listed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        ['id' => 12, 'qty' => 1, 'valueType' => 'fixed', 'value' => 19.99, 'customerGroupId' => 2, 'customerGroupName' => 'Wholesale', 'productId' => 1],
                                    ],
                                    'meta' => ['currentPage' => 1, 'perPage' => 1, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/catalog/products/{productId}/customer-group-prices',
            input: AdminCatalogProductCustomerGroupPriceCreateInput::class,
            processor: AdminCatalogProductCustomerGroupPriceProcessor::class,
            status: 201,
            requirements: ['productId' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Add a customer-group price to a product',
                description: 'Creates a new tier-price row. `customer_group_id: null` makes the price apply to every customer group. The combination `(qty, customer_group_id)` must be unique per product.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['qty', 'value_type', 'value'],
                                'properties' => [
                                    'qty'               => ['type' => 'integer', 'minimum' => 1, 'example' => 10],
                                    'value_type'        => ['type' => 'string', 'enum' => ['fixed', 'discount'], 'example' => 'discount'],
                                    'value'             => ['type' => 'number', 'example' => 15.0],
                                    'customer_group_id' => ['type' => 'integer', 'nullable' => true, 'example' => 2],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Tier-price row created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 12, 'qty' => 10, 'valueType' => 'discount', 'value' => 15.0, 'customerGroupId' => 2, 'productId' => 1],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failed (e.g. duplicate qty/customer-group, unknown group, qty < 1).'),
                    '404' => new Model\Response(description: 'Product not found.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/catalog/products/{productId}/customer-group-prices/{id}',
            input: AdminCatalogProductCustomerGroupPriceUpdateInput::class,
            provider: AdminCatalogProductCustomerGroupPriceProvider::class,
            processor: AdminCatalogProductCustomerGroupPriceProcessor::class,
            requirements: ['productId' => '\d+', 'id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Update a customer-group price row',
                description: 'Partially updates the given tier-price row. The new `(qty, customer_group_id)` combination must remain unique for the product.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('id', 'path', 'Customer-group-price row ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'qty'               => ['type' => 'integer', 'minimum' => 1, 'example' => 5],
                                    'value_type'        => ['type' => 'string', 'enum' => ['fixed', 'discount'], 'example' => 'fixed'],
                                    'value'             => ['type' => 'number', 'example' => 17.5],
                                    'customer_group_id' => ['type' => 'integer', 'nullable' => true, 'example' => null],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Tier-price row updated.'),
                    '404' => new Model\Response(description: 'Product or row not found, or row does not belong to product.'),
                    '422' => new Model\Response(description: 'Validation failed.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/catalog/products/{productId}/customer-group-prices/{id}',
            provider: AdminCatalogProductCustomerGroupPriceProvider::class,
            processor: AdminCatalogProductCustomerGroupPriceProcessor::class,
            requirements: ['productId' => '\d+', 'id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Delete a customer-group price row',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('id', 'path', 'Customer-group-price row ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Tier-price row deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Customer-group price deleted successfully.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Row not found or does not belong to product.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCatalogProductCustomerGroupPriceProvider::class,
            paginationEnabled: false,
            extraArgs: [
                'productId' => ['type' => 'Int!'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminCatalogProductCustomerGroupPriceCreateInput::class,
            processor: AdminCatalogProductCustomerGroupPriceProcessor::class,
            description: 'Add a customer-group price row to a product.',
            extraArgs: [
                'productId' => ['type' => 'Int!'],
            ],
        ),
        new Mutation(
            name: 'update',
            input: AdminCatalogProductCustomerGroupPriceUpdateInput::class,
            processor: AdminCatalogProductCustomerGroupPriceProcessor::class,
            description: 'Update a customer-group price row.',
            extraArgs: [
                'productId' => ['type' => 'Int!'],
            ],
        ),
        new Mutation(
            name: 'delete',
            input: AdminCatalogProductCustomerGroupPriceUpdateInput::class,
            processor: AdminCatalogProductCustomerGroupPriceProcessor::class,
            description: 'Delete a customer-group price row.',
            extraArgs: [
                'productId' => ['type' => 'Int!'],
            ],
        ),
    ],
)]
class AdminCatalogProductCustomerGroupPrice
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $product_id = null;

    #[ApiProperty(writable: false)]
    public ?int $qty = null;

    #[ApiProperty(writable: false)]
    public ?string $value_type = null;

    #[ApiProperty(writable: false)]
    public ?float $value = null;

    #[ApiProperty(writable: false)]
    public ?int $customer_group_id = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_group_name = null;
}
