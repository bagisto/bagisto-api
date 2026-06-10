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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsThemeWriteProvider;

/**
 * Admin Settings → Themes (theme customizations).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\ThemeController.
 *
 * Bagisto "Themes" are per-channel theme customization blocks (image carousels,
 * static content, footer links, etc.) — NOT a registry of installable themes.
 * Each row picks a `type` from a fixed set and stores arbitrary per-locale
 * `options` JSON via translation rows.
 *
 * REST:
 *   GET    /api/admin/settings/themes
 *   GET    /api/admin/settings/themes/{id}
 *   POST   /api/admin/settings/themes
 *   PUT    /api/admin/settings/themes/{id}
 *   DELETE /api/admin/settings/themes/{id}
 *
 * GraphQL: adminSettingsThemes, adminSettingsTheme,
 *          createAdminSettingsTheme, updateAdminSettingsTheme,
 *          deleteAdminSettingsTheme
 *
 * Deferred (v1):
 *   - Image uploads under `options.images[*].image` / `options.services[*].service_icon`
 *     and the `static_content` inline image-pick action. Accept path strings only.
 *   - The two-step admin upload flow (`store` with `id` param + multipart) — not exposed.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsTheme',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/themes',
            input: AdminSettingsThemeCreateInput::class,
            processor: AdminSettingsThemeProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Themes'],
                summary: 'Create a theme customization',
                description: 'Step-1 create: name, sort_order, type, channel_id, theme_code. Per-locale `options` are configured via PUT.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'sort_order', 'type', 'channel_id', 'theme_code'],
                                'properties' => [
                                    'name'       => ['type' => 'string', 'example' => 'Homepage Banner'],
                                    'sort_order' => ['type' => 'integer', 'example' => 1],
                                    'type'       => ['type' => 'string', 'enum' => ['product_carousel', 'category_carousel', 'static_content', 'image_carousel', 'footer_links', 'services_content'], 'example' => 'image_carousel'],
                                    'channel_id' => ['type' => 'integer', 'example' => 1],
                                    'theme_code' => ['type' => 'string', 'example' => 'default'],
                                    'status'     => ['type' => 'boolean', 'example' => true],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Theme customization created.'),
                    '422' => new Model\Response(description: 'Validation failure (missing field, invalid type, unknown channel_id).'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/themes/{id}',
            input: AdminSettingsThemeUpdateInput::class,
            provider: AdminSettingsThemeWriteProvider::class,
            processor: AdminSettingsThemeProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Themes'],
                summary: 'Update a theme customization',
                description: 'Updates a theme customization. Pass `locale` + `options` to write per-locale options.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Theme customization ID.', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name'       => ['type' => 'string'],
                                    'sort_order' => ['type' => 'integer'],
                                    'type'       => ['type' => 'string'],
                                    'channel_id' => ['type' => 'integer'],
                                    'theme_code' => ['type' => 'string'],
                                    'status'     => ['type' => 'boolean'],
                                    'locale'     => ['type' => 'string', 'example' => 'en'],
                                    'options'    => ['type' => 'object'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Theme updated.'),
                    '404' => new Model\Response(description: 'Theme not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/themes/{id}',
            provider: AdminSettingsThemeWriteProvider::class,
            processor: AdminSettingsThemeProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Themes'],
                summary: 'Delete a theme customization',
                parameters: [
                    new Model\Parameter('id', 'path', 'Theme customization ID.', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Theme deleted.'),
                    '404' => new Model\Response(description: 'Theme not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/themes/{id}',
            provider: AdminSettingsThemeItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Themes'],
                summary: 'Theme customization detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Theme customization ID.', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Single theme row with per-locale translations inlined.'),
                    '404' => new Model\Response(description: 'Theme not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/themes',
            provider: AdminSettingsThemeCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Themes'],
                summary: 'List theme customizations',
                description: 'Paginated, filterable, sortable list of theme customizations. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('name', 'query', 'Filter by name (partial match).', false, schema: ['type' => 'string']),
                    new Model\Parameter('type', 'query', 'Filter by type (exact).', false, schema: ['type' => 'string']),
                    new Model\Parameter('theme_code', 'query', 'Filter by theme_code (exact).', false, schema: ['type' => 'string']),
                    new Model\Parameter('channel_id', 'query', 'Filter by channel id.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('status', 'query', 'Filter by status (0/1).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'name', 'type', 'sort_order', 'theme_code', 'channel_id', 'status']]),
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
            provider: AdminSettingsThemeCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name'       => ['type' => 'String'],
                'type'       => ['type' => 'String'],
                'theme_code' => ['type' => 'String'],
                'channel_id' => ['type' => 'Int'],
                'status'     => ['type' => 'Int'],
                'sort'       => ['type' => 'String'],
                'order'      => ['type' => 'String'],
            ],
            description: 'Admin settings theme customizations listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsThemeItemProvider::class,
            description: 'Admin settings theme customization detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsThemeCreateInput::class,
            processor: AdminSettingsThemeProcessor::class,
            description: 'Create a theme customization. Becomes createAdminSettingsTheme.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsThemeUpdateInput::class,
            processor: AdminSettingsThemeProcessor::class,
            description: 'Update a theme customization. Becomes updateAdminSettingsTheme.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsThemeUpdateInput::class,
            processor: AdminSettingsThemeProcessor::class,
            description: 'Delete a theme customization. Becomes deleteAdminSettingsTheme.',
        ),
    ],
)]
class AdminSettingsTheme
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false, example: 1)]
    public ?int $id = null;

    #[ApiProperty(writable: false, example: 'Home Page Carousel')]
    public ?string $name = null;

    #[ApiProperty(writable: false, example: 'image_carousel')]
    public ?string $type = null;

    #[ApiProperty(writable: false, example: 1)]
    public ?int $sort_order = null;

    #[ApiProperty(writable: false, example: true)]
    public ?bool $status = null;

    #[ApiProperty(writable: false, example: 1)]
    public ?int $channel_id = null;

    #[ApiProperty(writable: false, example: 'default')]
    public ?string $theme_code = null;

    /** @var array<int, array{locale:string, options:array|null}>|null */
    #[ApiProperty(writable: false, example: [['locale' => 'en', 'options' => ['images' => [['image' => 'theme/1/slide.png', 'link' => 'https://example.com']]]]])]
    public ?array $translations = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:15:00+00:00')]
    public ?string $created_at = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:20:00+00:00')]
    public ?string $updated_at = null;
}
