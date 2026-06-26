<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\AdminConfigurationTreeItem;
use Webkul\BagistoApi\Admin\Resolver\AdminConfigurationMenuQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationMenuProvider;

/**
 * Admin Configuration menu — returns the merged system_config tree (schema).
 *
 * REST   : GET /api/admin/configuration/menu
 * GraphQL: adminConfigurationMenu query
 *
 * Optional `?slug=` scopes the response to one menu node (a section or group).
 * `?include_values=true` embeds the currently-effective value per field, using
 * `?channel=` and `?locale=` to scope the lookup (defaults to the request's
 * channel/locale resolution).
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminConfigurationMenu',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/configuration/menu',
            provider: AdminConfigurationMenuProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Configuration'],
                summary: 'Configuration menu (schema tree)',
                description: 'Returns the merged system_config tree. Use `?slug=<section>.<group>` to scope to one node. `?include_values=true` embeds the effective value per field (uses `?channel=` and `?locale=` for resolution).',
                parameters: [
                    new Model\Parameter('slug', 'query', 'Optional slug filter, e.g. `sales.order_settings`.', false, schema: ['type' => 'string']),
                    new Model\Parameter('include_values', 'query', 'When true, embeds the effective value per field.', false, schema: ['type' => 'boolean']),
                    new Model\Parameter('channel', 'query', 'Channel code for value resolution.', false, schema: ['type' => 'string']),
                    new Model\Parameter('locale', 'query', 'Locale code for value resolution.', false, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Configuration menu tree.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'key'      => 'general',
                                    'name'     => 'General',
                                    'info'     => null,
                                    'sort'     => 1,
                                    'icon'     => null,
                                    'children' => [[
                                        'key'      => 'general.general',
                                        'name'     => 'General',
                                        'sort'     => 1,
                                        'children' => [[
                                            'key'    => 'general.general.locale_options',
                                            'name'   => 'Locale Options',
                                            'fields' => [[
                                                'name'         => 'weight_unit',
                                                'code'         => 'general.general.locale_options.weight_unit',
                                                'title'        => 'Weight Unit',
                                                'type'         => 'select',
                                                'default'      => 'kgs',
                                                'channelBased' => true,
                                                'localeBased'  => false,
                                                'validation'   => null,
                                                'options'      => [
                                                    ['title' => 'lbs', 'value' => 'lbs'],
                                                    ['title' => 'kgs', 'value' => 'kgs'],
                                                ],
                                            ]],
                                        ]],
                                    ]],
                                ]],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Slug not found in the registered config tree.'),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'menu',
            resolver: AdminConfigurationMenuQueryResolver::class,
            args: [
                'slug'           => ['type' => 'String'],
                'include_values' => ['type' => 'Boolean'],
                'channel'        => ['type' => 'String'],
                'locale'         => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Returns the configuration menu tree. Optional `slug` scope and `include_values` flag.',
        ),
    ],
)]
class AdminConfigurationMenu
{
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $id = 'configuration-menu';

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $slug = null;

    /**
     * @var AdminConfigurationTreeItem[]|null
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $tree = null;
}
