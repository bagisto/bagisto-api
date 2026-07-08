<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsLocaleMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleMassDeleteProcessor;

/**
 * Mass-delete admin settings locales.
 *
 * REST:    POST /api/admin/settings/locales/mass-delete
 * GraphQL: createAdminSettingsLocaleMassDelete
 *
 * Per-id guards (skip + report or fail row-by-row): would-be-last-locale,
 * channel-default. Non-existent IDs are silently skipped.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsLocaleMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/locales/mass-delete',
            input: AdminSettingsLocaleMassDeleteInput::class,
            processor: AdminSettingsLocaleMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Locales'],
                summary: 'Mass delete locales',
                description: 'Deletes a batch of locales. Non-existent IDs are silently skipped; locales that are last-remaining or channel defaults are skipped with a reason in the response.',
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
                                        'example' => [5, 7],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Locales deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [5, 7],
                                    'message' => 'Locales deleted successfully.',
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
            input: AdminSettingsLocaleMassDeleteInput::class,
            processor: AdminSettingsLocaleMassDeleteProcessor::class,
            description: 'Mass-delete a batch of locales. Becomes createAdminSettingsLocaleMassDelete.',
        ),
    ],
)]
class AdminSettingsLocaleMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    /** @var array<int, array{id:int,reason:string}>|null */
    #[ApiProperty(writable: false)]
    public ?array $skipped = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
