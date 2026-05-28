<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsInventorySourceCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsInventorySourceUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceWriteProvider;

/**
 * Admin Settings → Inventory Sources endpoints (Block B Wave 1).
 *
 * REST:
 *   GET    /api/admin/settings/inventory-sources
 *   GET    /api/admin/settings/inventory-sources/{id}
 *   POST   /api/admin/settings/inventory-sources
 *   PUT    /api/admin/settings/inventory-sources/{id}
 *   DELETE /api/admin/settings/inventory-sources/{id}
 *
 * GraphQL: adminSettingsInventorySources, adminSettingsInventorySource,
 *          createAdminSettingsInventorySource, updateAdminSettingsInventorySource,
 *          deleteAdminSettingsInventorySource
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\InventorySourceController.
 *
 * Delete guards (parity with monolith + API-specific FK guard):
 *  - 400 if this is the last remaining inventory source.
 *  - 400 if any product_inventories row references this source.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsInventorySource',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/inventory-sources',
            input: AdminSettingsInventorySourceCreateInput::class,
            processor: AdminSettingsInventorySourceProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Create a new inventory source',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['code', 'name', 'contact_name', 'contact_email', 'contact_number', 'country', 'state', 'city', 'street', 'postcode'],
                                'properties' => [
                                    'code'           => ['type' => 'string', 'example' => 'warehouse-1'],
                                    'name'           => ['type' => 'string', 'example' => 'Warehouse 1'],
                                    'description'    => ['type' => 'string', 'example' => 'Primary warehouse.'],
                                    'contact_name'   => ['type' => 'string', 'example' => 'Jane Doe'],
                                    'contact_email'  => ['type' => 'string', 'example' => 'jane@example.com'],
                                    'contact_number' => ['type' => 'string', 'example' => '1234567890'],
                                    'contact_fax'    => ['type' => 'string', 'example' => null],
                                    'country'        => ['type' => 'string', 'example' => 'US'],
                                    'state'          => ['type' => 'string', 'example' => 'CA'],
                                    'city'           => ['type' => 'string', 'example' => 'Los Angeles'],
                                    'street'         => ['type' => 'string', 'example' => '123 Main St'],
                                    'postcode'       => ['type' => 'string', 'example' => '90001'],
                                    'priority'       => ['type' => 'integer', 'example' => 0],
                                    'latitude'       => ['type' => 'number', 'format' => 'float', 'example' => 34.05],
                                    'longitude'      => ['type' => 'number', 'format' => 'float', 'example' => -118.24],
                                    'status'         => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Inventory source created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/inventory-sources/{id}',
            input: AdminSettingsInventorySourceUpdateInput::class,
            provider: AdminSettingsInventorySourceWriteProvider::class,
            processor: AdminSettingsInventorySourceProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Update an inventory source',
                parameters: [
                    new Model\Parameter('id', 'path', 'Inventory source ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name'     => ['type' => 'string', 'example' => 'Warehouse 1 (renamed)'],
                                    'priority' => ['type' => 'integer', 'example' => 5],
                                    'status'   => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Inventory source updated.'),
                    '404' => new Model\Response(description: 'Inventory source not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/inventory-sources/{id}',
            provider: AdminSettingsInventorySourceWriteProvider::class,
            processor: AdminSettingsInventorySourceProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Delete an inventory source',
                parameters: [
                    new Model\Parameter('id', 'path', 'Inventory source ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Inventory source deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Inventory source deleted successfully.'],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Last remaining source, or referenced by product_inventories.'),
                    '404' => new Model\Response(description: 'Inventory source not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/inventory-sources/{id}',
            provider: AdminSettingsInventorySourceItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Inventory source detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Inventory source ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Single inventory source.'),
                    '404' => new Model\Response(description: 'Inventory source not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/inventory-sources',
            provider: AdminSettingsInventorySourceCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'List inventory sources',
                description: 'Paginated, filterable, sortable list of inventory sources. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('code', 'query', 'Filter by code (partial match).', false, schema: ['type' => 'string']),
                    new Model\Parameter('name', 'query', 'Filter by name (partial match).', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', '0 or 1.', false, schema: ['type' => 'integer', 'enum' => [0, 1]]),
                    new Model\Parameter('country', 'query', 'Exact country code.', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'code', 'name', 'priority', 'status']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in the { data, meta } envelope.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsInventorySourceCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'code'    => ['type' => 'String'],
                'name'    => ['type' => 'String'],
                'status'  => ['type' => 'Int'],
                'country' => ['type' => 'String'],
                'sort'    => ['type' => 'String'],
                'order'   => ['type' => 'String'],
            ],
            description: 'Admin settings inventory-sources listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsInventorySourceItemProvider::class,
            description: 'Admin settings inventory-source detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsInventorySourceCreateInput::class,
            processor: AdminSettingsInventorySourceProcessor::class,
            description: 'Create a new inventory source. Becomes createAdminSettingsInventorySource.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsInventorySourceUpdateInput::class,
            processor: AdminSettingsInventorySourceProcessor::class,
            description: 'Update an inventory source. Becomes updateAdminSettingsInventorySource.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsInventorySourceUpdateInput::class,
            processor: AdminSettingsInventorySourceProcessor::class,
            description: 'Delete an inventory source. Becomes deleteAdminSettingsInventorySource.',
        ),
    ],
)]
class AdminSettingsInventorySource
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $code = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $contactName = null;

    #[ApiProperty(writable: false)]
    public ?string $contactEmail = null;

    #[ApiProperty(writable: false)]
    public ?string $contactNumber = null;

    #[ApiProperty(writable: false)]
    public ?string $contactFax = null;

    #[ApiProperty(writable: false)]
    public ?string $country = null;

    #[ApiProperty(writable: false)]
    public ?string $state = null;

    #[ApiProperty(writable: false)]
    public ?string $city = null;

    #[ApiProperty(writable: false)]
    public ?string $street = null;

    #[ApiProperty(writable: false)]
    public ?string $postcode = null;

    #[ApiProperty(writable: false)]
    public ?int $priority = null;

    #[ApiProperty(writable: false)]
    public ?float $latitude = null;

    #[ApiProperty(writable: false)]
    public ?float $longitude = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;
}
