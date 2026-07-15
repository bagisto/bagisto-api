<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeMassDeleteProcessor;

/**
 * Mass-delete admin settings theme customizations.
 *
 * REST:    POST /api/admin/settings/themes/mass-delete
 * GraphQL: createAdminSettingsThemeMassDelete
 *
 * Mirrors Bagisto admin ThemeController::massDestroy. Non-existent IDs are
 * silently skipped (matches monolith — best-effort loop).
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsThemeMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/themes/mass-delete',
            input: AdminSettingsThemeMassDeleteInput::class,
            processor: AdminSettingsThemeMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Themes'],
                summary: 'Mass delete theme customizations',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [3, 4]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Themes deleted.'),
                    '422' => new Model\Response(description: 'Empty indices.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminSettingsThemeMassDeleteInput::class,
            processor: AdminSettingsThemeMassDeleteProcessor::class,
            description: 'Mass-delete theme customizations. Becomes createAdminSettingsThemeMassDelete.',
        ),
    ],
)]
class AdminSettingsThemeMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
