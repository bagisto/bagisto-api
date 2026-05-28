<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsExchangeRateMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsExchangeRateMassDeleteProcessor;

/**
 * Mass-delete admin settings exchange rates.
 *
 * REST:    POST /api/admin/settings/exchange-rates/mass-delete
 * GraphQL: createAdminSettingsExchangeRateMassDelete
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsExchangeRateMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/exchange-rates/mass-delete',
            input: AdminSettingsExchangeRateMassDeleteInput::class,
            processor: AdminSettingsExchangeRateMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Mass delete exchange rates',
                description: 'Deletes a batch of exchange rates. Non-existent IDs are silently skipped.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['indices'],
                                'properties' => [
                                    'indices' => [
                                        'type'    => 'array',
                                        'items'   => ['type' => 'integer'],
                                        'example' => [4, 7],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Exchange rates deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [4, 7],
                                    'message' => 'Exchange rates deleted successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Empty indices.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminSettingsExchangeRateMassDeleteInput::class,
            processor: AdminSettingsExchangeRateMassDeleteProcessor::class,
            description: 'Mass-delete a batch of exchange rates. Becomes createAdminSettingsExchangeRateMassDelete.',
        ),
    ],
)]
class AdminSettingsExchangeRateMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
