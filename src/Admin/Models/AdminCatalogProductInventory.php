<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductInventoryUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductInventoryProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductInventoryProvider;

/**
 * Per-source inventory row for a catalog product.
 *
 * REST:
 *   GET /api/admin/catalog/products/{productId}/inventories
 *     200: { data: [{ id, sourceId, sourceCode, sourceName, qty }, ...], meta: { ..., totalQty } }
 *
 *   PUT /api/admin/catalog/products/{productId}/inventories
 *     Body: { "inventories": { "1": 25, "2": 0, "3": 10 } }
 *     200: same listing payload as GET (totals refreshed)
 *
 * GraphQL:
 *   adminCatalogProductInventories(productId: Int!) — cursor connection of rows.
 *   updateAdminCatalogProductInventories(input: { productId: Int!, inventories: ... }).
 *
 * Bulk-upsert semantics — mirrors Bagisto core:
 *   - Pass an inventory source id with a positive qty → upsert that source's qty.
 *   - Pass an inventory source id with qty=0 → row is kept but zeroed-out
 *     (saveInventories writes 0; the row is NOT deleted unless the underlying
 *     repository decides to). Sources NOT included in the request are left
 *     untouched.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProductInventory',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/catalog/products/{productId}/inventories',
            provider: AdminCatalogProductInventoryProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'List per-source inventory rows for a product',
                description: 'Returns one row per inventory_source that has a product_inventories entry for this product. The envelope meta carries totalQty — the sum across all sources.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Product ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Inventory rows for the product.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'         => 14,
                                            'sourceId'   => 1,
                                            'sourceCode' => 'default',
                                            'sourceName' => 'Default',
                                            'qty'        => 25,
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 1,
                                        'lastPage'    => 1,
                                        'total'       => 1,
                                        'from'        => 1,
                                        'to'          => 1,
                                        'totalQty'    => 25,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '404' => new Model\Response(description: 'Product not found.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/catalog/products/{productId}/inventories',
            input: AdminCatalogProductInventoryUpdateInput::class,
            provider: AdminCatalogProductInventoryProvider::class,
            processor: AdminCatalogProductInventoryProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: "Bulk-update a product's per-source inventory quantities",
                description: 'Mirrors Bagisto monolith ProductController::updateInventories. Fires catalog.product.update.before / catalog.product.update.after. Returns the updated listing payload — same shape as GET.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Product ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['inventories'],
                                'properties' => [
                                    'inventories' => [
                                        'type'                 => 'object',
                                        'description'          => 'Map of inventory_source_id → quantity. Use 0 to zero-out a source.',
                                        'additionalProperties' => ['type' => 'integer', 'minimum' => 0],
                                    ],
                                ],
                            ],
                            'example' => [
                                'inventories' => ['1' => 25, '2' => 0],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Inventories saved. Returns the refreshed listing payload.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'         => 14,
                                            'sourceId'   => 1,
                                            'sourceCode' => 'default',
                                            'sourceName' => 'Default',
                                            'qty'        => 25,
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 1,
                                        'lastPage'    => 1,
                                        'total'       => 1,
                                        'from'        => 1,
                                        'to'          => 1,
                                        'totalQty'    => 25,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks catalog.products.edit.'),
                    '404' => new Model\Response(description: 'Product not found.'),
                    '422' => new Model\Response(description: 'Validation failed — missing inventories, unknown inventory_source_id, or negative quantity.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCatalogProductInventoryProvider::class,
            paginationType: 'cursor',
            description: 'List per-source inventory rows for a product (becomes adminCatalogProductInventories in GraphQL).',
            args: [
                'productId' => ['type' => 'Int!', 'description' => 'Product ID'],
            ],
        ),
        new Mutation(
            name: 'update',
            input: AdminCatalogProductInventoryUpdateInput::class,
            output: self::class,
            processor: AdminCatalogProductInventoryProcessor::class,
            description: 'Bulk-update inventories for a product (becomes updateAdminCatalogProductInventory in GraphQL). API Platform auto-injects `id: ID!` on the input — callers must pass a plausible IRI string (e.g. `/api/admin/catalog/products/{productId}/inventories`); the processor reads productId from the input, not from the IRI. The result is a single row for the first updated source — read `_id`/`sourceId`/`sourceCode`/`sourceName`/`qty`. Do NOT select the IRI `id` field (this is a routeless parent-scoped sub-resource — it has no per-row route and cannot resolve over a mutation payload). Re-query `adminCatalogProductInventories(productId:)` for the full refreshed list.',
        ),
    ]
)]
class AdminCatalogProductInventory
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false, description: 'product_inventories row id.')]
    public ?int $id = null;

    #[ApiProperty(writable: false, description: 'inventory_source_id this row belongs to.')]
    public ?int $source_id = null;

    #[ApiProperty(writable: false, description: 'Inventory source code (e.g. "default").')]
    public ?string $source_code = null;

    #[ApiProperty(writable: false, description: 'Inventory source display name.')]
    public ?string $source_name = null;

    #[ApiProperty(writable: false, description: 'Quantity on hand at this source.')]
    public ?int $qty = null;
}
