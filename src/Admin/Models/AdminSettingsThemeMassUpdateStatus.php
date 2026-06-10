<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeMassUpdateStatusProcessor;

/**
 * Mass-update status admin settings theme customizations.
 *
 * REST:    POST /api/admin/settings/themes/mass-update-status
 * GraphQL: createAdminSettingsThemeMassUpdateStatus
 *
 * Mirrors Bagisto admin ThemeController::massUpdate which calls
 * ThemeCustomizationRepository::massUpdateStatus.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsThemeMassUpdateStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/themes/mass-update-status',
            input: AdminSettingsThemeMassUpdateStatusInput::class,
            processor: AdminSettingsThemeMassUpdateStatusProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Themes'],
                summary: 'Mass update status of theme customizations',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['indices', 'value'],
                                'properties' => [
                                    'indices' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [3, 4]],
                                    'value'   => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Statuses updated.'),
                    '422' => new Model\Response(description: 'Invalid input.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminSettingsThemeMassUpdateStatusInput::class,
            processor: AdminSettingsThemeMassUpdateStatusProcessor::class,
            description: 'Mass-update status of theme customizations. Becomes createAdminSettingsThemeMassUpdateStatus.',
        ),
    ],
)]
class AdminSettingsThemeMassUpdateStatus
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $updated = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
