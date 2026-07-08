<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsInventorySourceMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsInventorySourceMassDeleteProcessor;

/**
 * Mass-delete admin settings inventory sources.
 *
 * REST:    POST /api/admin/settings/inventory-sources/mass-delete
 * GraphQL: createAdminSettingsInventorySourceMassDelete
 *
 * Guards: refuses to delete every remaining source (must leave at least 1);
 * refuses any id that is referenced by product_inventories.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsInventorySourceMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/inventory-sources/mass-delete',
            input: AdminSettingsInventorySourceMassDeleteInput::class,
            processor: AdminSettingsInventorySourceMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Inventory Sources'],
                summary: 'Mass delete inventory sources',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices'],
                                'properties' => [
                                    'indices' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'example' => [2, 3],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Inventory sources deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [2, 3],
                                    'message' => 'Inventory sources deleted successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Would leave zero sources, or one of the IDs is in use.'),
                    '422' => new Model\Response(description: 'Empty indices.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminSettingsInventorySourceMassDeleteInput::class,
            processor: AdminSettingsInventorySourceMassDeleteProcessor::class,
            description: 'Mass-delete a batch of inventory sources. Becomes createAdminSettingsInventorySourceMassDelete.',
        ),
    ],
)]
class AdminSettingsInventorySourceMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
