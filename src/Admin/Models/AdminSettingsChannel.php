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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsChannelUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsChannelWriteProvider;

/**
 * Admin Settings → Channels endpoints (Block B Wave 2).
 *
 * REST:
 *   GET    /api/admin/settings/channels            — datagrid-parity listing
 *   GET    /api/admin/settings/channels/{id}       — detail (with translations + locales + currencies)
 *   POST   /api/admin/settings/channels            — create
 *   PUT    /api/admin/settings/channels/{id}       — update (translatable via translations map)
 *   DELETE /api/admin/settings/channels/{id}       — delete (guards: last channel, app.channel)
 *
 * GraphQL:
 *   adminSettingsChannels            — cursor listing
 *   adminSettingsChannel(id:)        — detail
 *   createAdminSettingsChannel       — create
 *   updateAdminSettingsChannel       — update
 *   deleteAdminSettingsChannel       — delete
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\ChannelController 1:1.
 *
 * Image uploads (logo/favicon) deferred — accept storage paths only, mirrors
 * the Phase 5.11 image-upload-deferral pattern.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsChannel',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/channels',
            input: AdminSettingsChannelCreateInput::class,
            processor: AdminSettingsChannelProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Create a new channel',
                description: 'Mirrors Bagisto admin Settings → Channels → Create. Validates code (unique, alpha-dash), hostname (unique), locales/currencies/inventory_sources (non-empty arrays), default_locale_id and base_currency_id (must appear in the respective arrays), root_category_id (must exist).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => [
                                'code'              => 'us_store',
                                'name'              => 'US Store',
                                'hostname'          => 'us.example.com',
                                'theme'             => 'default',
                                'timezone'          => 'America/New_York',
                                'locales'           => [1],
                                'default_locale_id' => 1,
                                'currencies'        => [1],
                                'base_currency_id'  => 1,
                                'inventory_sources' => [1],
                                'root_category_id'  => 1,
                                'seo_title'         => 'Welcome to the US store',
                                'seo_description'   => 'Best products for the US market',
                                'seo_keywords'      => 'shop, us, store',
                                'is_maintenance_on' => false,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Channel created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/channels/{id}',
            input: AdminSettingsChannelUpdateInput::class,
            provider: AdminSettingsChannelWriteProvider::class,
            processor: AdminSettingsChannelProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Update a channel',
                description: 'Code/hostname uniqueness excludes the current id. Use the `translations` map for locale-nested attributes (name, description, home_page_content, footer_content, seo_*, maintenance_mode_text). Top-level scalar fields broadcast to every configured locale via the repository.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => [
                                'code'              => 'us_store',
                                'hostname'          => 'us.example.com',
                                'locales'           => [1],
                                'default_locale_id' => 1,
                                'currencies'        => [1],
                                'base_currency_id'  => 1,
                                'inventory_sources' => [1],
                                'root_category_id'  => 1,
                                'translations'      => [
                                    'en' => [
                                        'name'            => 'US Store',
                                        'description'     => 'Our US storefront',
                                        'seo_title'       => 'Welcome',
                                        'seo_description' => 'Welcome to our shop',
                                        'seo_keywords'    => 'shop, us',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Channel updated.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/channels/{id}',
            provider: AdminSettingsChannelWriteProvider::class,
            processor: AdminSettingsChannelProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Delete a channel',
                description: 'Refuses with HTTP 400 if this is the only remaining channel OR if its code matches the application-wide default channel (config("app.channel")).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Channel deleted.'),
                    '400' => new Model\Response(description: 'Cannot delete — last channel or default app channel.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/channels/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsChannelItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Channel detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Channel ID.', true, schema: ['type' => 'integer', 'example' => 3]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Single channel with translations, locales, currencies, inventory sources.'),
                    '404' => new Model\Response(description: 'Channel not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/channels',
            provider: AdminSettingsChannelCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'List channels (datagrid parity)',
                description: 'Paginated, filterable, sortable channels list. Filters: code, name, hostname. Sort: id, code, name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('code', 'query', 'Partial code match.', false, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string', 'example' => 'US']),
                    new Model\Parameter('hostname', 'query', 'Partial hostname match.', false, schema: ['type' => 'string', 'example' => 'example.com']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'code', 'name'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated channels in the { data, meta } envelope.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsChannelCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'code'     => ['type' => 'String'],
                'name'     => ['type' => 'String'],
                'hostname' => ['type' => 'String'],
                'sort'     => ['type' => 'String'],
                'order'    => ['type' => 'String'],
            ],
            description: 'Admin channels listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsChannelItemProvider::class,
            description: 'Admin channel detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsChannelCreateInput::class,
            processor: AdminSettingsChannelProcessor::class,
            description: 'Create a new channel.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsChannelUpdateInput::class,
            processor: AdminSettingsChannelProcessor::class,
            description: 'Update a channel.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsChannelUpdateInput::class,
            processor: AdminSettingsChannelProcessor::class,
            description: 'Delete a channel. Refused for the last channel or the default app channel.',
        ),
    ],
)]
class AdminSettingsChannel
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
    public ?string $hostname = null;

    #[ApiProperty(writable: false)]
    public ?string $theme = null;

    #[ApiProperty(writable: false)]
    public ?string $timezone = null;

    #[ApiProperty(writable: false)]
    public ?int $defaultLocaleId = null;

    #[ApiProperty(writable: false)]
    public ?int $baseCurrencyId = null;

    #[ApiProperty(writable: false)]
    public ?int $rootCategoryId = null;

    #[ApiProperty(writable: false)]
    public ?bool $isMaintenanceOn = null;

    #[ApiProperty(writable: false)]
    public ?string $maintenanceModeText = null;

    #[ApiProperty(writable: false)]
    public ?string $allowedIps = null;

    #[ApiProperty(writable: false)]
    public ?string $logo = null;

    #[ApiProperty(writable: false)]
    public ?string $logoUrl = null;

    #[ApiProperty(writable: false)]
    public ?string $favicon = null;

    #[ApiProperty(writable: false)]
    public ?string $faviconUrl = null;

    /** @var array<int>|null */
    #[ApiProperty(writable: false)]
    public ?array $localeIds = null;

    /** @var array<int>|null */
    #[ApiProperty(writable: false)]
    public ?array $currencyIds = null;

    /** @var array<int>|null */
    #[ApiProperty(writable: false)]
    public ?array $inventorySourceIds = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $homeSeo = null;

    /** @var array<int,array<string,mixed>>|null */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;
}
