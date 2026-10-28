<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsLocaleMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleMassDeleteProcessor;

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
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The ids of the locales that were deleted.
     *
     * @var int[]|null
     */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    /**
     * The locales that were not deleted, each with the reason.
     *
     * @var array<int, array{id:int,reason:string}>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $skipped = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
