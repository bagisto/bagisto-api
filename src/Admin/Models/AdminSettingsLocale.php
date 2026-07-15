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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsLocaleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsLocaleUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsLocaleWriteProvider;

/**
 * Admin Settings → Locales endpoints (Block B Wave 1).
 *
 * REST:
 *   GET    /api/admin/settings/locales
 *   GET    /api/admin/settings/locales/{id}
 *   POST   /api/admin/settings/locales
 *   PUT    /api/admin/settings/locales/{id}
 *   DELETE /api/admin/settings/locales/{id}
 *
 * GraphQL: adminSettingsLocales, adminSettingsLocale,
 *          createAdminSettingsLocale, updateAdminSettingsLocale,
 *          deleteAdminSettingsLocale
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\LocaleController.
 *
 * Notes:
 *  - logo_path image upload is intentionally deferred in v1 — accepts a path
 *    string only. Use the admin panel for now if a real upload is needed.
 *  - Delete refuses if the locale is the only remaining locale (400) or
 *    if it is any channel's default_locale_id (400).
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsLocale',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/locales',
            input: AdminSettingsLocaleCreateInput::class,
            processor: AdminSettingsLocaleProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Locales'],
                summary: 'Create a new locale',
                description: 'Creates a locale. Code must be unique and consist of lowercase letters/digits.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'name', 'direction'],
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'fr'],
                                    'name' => ['type' => 'string', 'example' => 'French'],
                                    'direction' => ['type' => 'string', 'enum' => ['ltr', 'rtl'], 'example' => 'ltr'],
                                    'logo_path' => ['type' => 'string', 'nullable' => true, 'example' => 'locales/fr.png'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Locale created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 5,
                                    'code' => 'fr',
                                    'name' => 'French',
                                    'direction' => 'ltr',
                                    'logoPath' => null,
                                    'logoUrl' => null,
                                    'createdAt' => '2026-05-22T08:15:00+00:00',
                                    'updatedAt' => '2026-05-22T08:15:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure (missing/duplicate code, invalid direction).'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/locales/{id}',
            input: AdminSettingsLocaleUpdateInput::class,
            provider: AdminSettingsLocaleWriteProvider::class,
            processor: AdminSettingsLocaleProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Locales'],
                summary: 'Update a locale',
                description: 'Updates a locale. Code uniqueness excludes self.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Locale ID.', true, schema: ['type' => 'integer', 'example' => 5]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'fr'],
                                    'name' => ['type' => 'string', 'example' => 'French (FR)'],
                                    'direction' => ['type' => 'string', 'enum' => ['ltr', 'rtl'], 'example' => 'ltr'],
                                    'logo_path' => ['type' => 'string', 'nullable' => true],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Locale updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 5,
                                    'code' => 'fr',
                                    'name' => 'French (FR)',
                                    'direction' => 'ltr',
                                    'logoPath' => 'locales/fr.png',
                                    'logoUrl' => 'https://your-domain.com/storage/locales/fr.png',
                                    'createdAt' => '2026-05-22T08:15:00+00:00',
                                    'updatedAt' => '2026-05-25T09:30:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Locale not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/locales/{id}',
            provider: AdminSettingsLocaleWriteProvider::class,
            processor: AdminSettingsLocaleProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Locales'],
                summary: 'Delete a locale',
                description: 'Refuses if this is the only remaining locale or if a channel uses it as its default locale.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Locale ID.', true, schema: ['type' => 'integer', 'example' => 5]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Locale deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Locale deleted successfully.'],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Refused — last locale or channel default.'),
                    '404' => new Model\Response(description: 'Locale not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/locales/{id}',
            provider: AdminSettingsLocaleItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Locales'],
                summary: 'Locale detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Locale ID.', true, schema: ['type' => 'integer', 'example' => 5]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Single locale row.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 5,
                                    'code' => 'fr',
                                    'name' => 'French',
                                    'direction' => 'ltr',
                                    'logoPath' => null,
                                    'logoUrl' => null,
                                    'createdAt' => '2026-05-22T08:15:00+00:00',
                                    'updatedAt' => '2026-05-22T08:15:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Locale not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/locales',
            provider: AdminSettingsLocaleCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Locales'],
                summary: 'List locales',
                description: 'Paginated, filterable, sortable list of locales. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('id', 'query', 'Filter by id (exact). Accepts a single id or a comma-separated list, e.g. 1,10,35.', false, schema: ['type' => 'string', 'example' => '1']),
                    new Model\Parameter('code', 'query', 'Filter by code (partial match).', false, schema: ['type' => 'string', 'example' => 'en']),
                    new Model\Parameter('name', 'query', 'Filter by name (partial match).', false, schema: ['type' => 'string', 'example' => 'Eng']),
                    new Model\Parameter('direction', 'query', 'Filter by direction (ltr/rtl).', false, schema: ['type' => 'string', 'enum' => ['ltr', 'rtl']]),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'code', 'name'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'asc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 1,
                                            'code' => 'en',
                                            'name' => 'English',
                                            'direction' => 'ltr',
                                            'logoPath' => 'locales/en.png',
                                            'logoUrl' => 'https://your-domain.com/storage/locales/en.png',
                                            'createdAt' => null,
                                            'updatedAt' => null,
                                        ],
                                        [
                                            'id' => 10,
                                            'code' => 'ar',
                                            'name' => 'Arabic',
                                            'direction' => 'rtl',
                                            'logoPath' => null,
                                            'logoUrl' => null,
                                            'createdAt' => '2026-05-22T08:15:00+00:00',
                                            'updatedAt' => '2026-05-22T08:15:00+00:00',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 1,
                                        'total' => 2,
                                        'from' => 1,
                                        'to' => 2,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsLocaleCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'id' => ['type' => 'String'],
                'code' => ['type' => 'String'],
                'name' => ['type' => 'String'],
                'direction' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin settings locales listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsLocaleItemProvider::class,
            description: 'Admin settings locale detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsLocaleCreateInput::class,
            processor: AdminSettingsLocaleProcessor::class,
            description: 'Create a new locale. Becomes createAdminSettingsLocale.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsLocaleUpdateInput::class,
            processor: AdminSettingsLocaleProcessor::class,
            description: 'Update a locale. Becomes updateAdminSettingsLocale.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsLocaleUpdateInput::class,
            processor: AdminSettingsLocaleProcessor::class,
            description: 'Delete a locale. Becomes deleteAdminSettingsLocale.',
        ),
    ],
)]
class AdminSettingsLocale
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false, example: 1)]
    public ?int $id = null;

    #[ApiProperty(writable: false, example: 'en')]
    public ?string $code = null;

    #[ApiProperty(writable: false, example: 'English')]
    public ?string $name = null;

    #[ApiProperty(writable: false, example: 'ltr')]
    public ?string $direction = null;

    #[ApiProperty(writable: false, example: 'locales/1/flag.png')]
    public ?string $logo_path = null;

    #[ApiProperty(writable: false, example: 'https://your-domain.com/storage/locales/1/flag.png')]
    public ?string $logo_url = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:15:00+00:00')]
    public ?string $created_at = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:20:00+00:00')]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false, example: 'Locale deleted successfully.')]
    public ?string $message = null;
}
