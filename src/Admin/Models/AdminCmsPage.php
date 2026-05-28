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
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminCmsPageCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCmsPageItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCmsPageProcessor;
use Webkul\BagistoApi\Admin\State\AdminCmsPageWriteProvider;

/**
 * Admin CMS → Pages endpoints (CMS Phase 1 read-only + CMS Phase 2 CRUD).
 *
 * REST:
 *   GET    /api/admin/cms/pages          — listing (datagrid parity)
 *   GET    /api/admin/cms/pages/{id}     — detail (translations + channels)
 *   POST   /api/admin/cms/pages          — create (top-level fields broadcast to all locales)
 *   PUT    /api/admin/cms/pages/{id}     — update (locale-nested payload)
 *   DELETE /api/admin/cms/pages/{id}     — delete
 *
 * GraphQL:
 *   adminCmsPages           — cursor listing
 *   adminCmsPage(id:)       — detail
 *   createAdminCmsPage      — create
 *   updateAdminCmsPage      — update
 *   deleteAdminCmsPage      — delete
 *
 * Mirrors Webkul\Admin\Http\Controllers\CMS\PageController 1:1.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCmsPage',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/cms/pages',
            input: AdminCmsPageCreateInput::class,
            processor: AdminCmsPageProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin CMS'],
                summary: 'Create a new CMS page',
                description: 'Mirrors Bagisto admin CMS → Pages → Create. Top-level translated fields (page_title, html_content, etc.) are broadcast to every locale by the PageRepository. Validates url_key (required + unique on cms_page_translations + slug regex), page_title, html_content, and channels (non-empty array of existing channel ids).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['url_key', 'page_title', 'html_content', 'channels'],
                                'properties' => [
                                    'url_key'          => ['type' => 'string', 'example' => 'about-us'],
                                    'page_title'       => ['type' => 'string', 'example' => 'About Us'],
                                    'html_content'     => ['type' => 'string', 'example' => '<h1>About Us</h1><p>Welcome.</p>'],
                                    'channels'         => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'meta_title'       => ['type' => 'string', 'example' => 'About Us'],
                                    'meta_keywords'    => ['type' => 'string', 'example' => 'about,us,company'],
                                    'meta_description' => ['type' => 'string', 'example' => 'Learn more about our company.'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Page created. Returns the same shape as GET /cms/pages/{id}.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'              => 7,
                                    'urlKey'          => 'about-us',
                                    'pageTitle'       => 'About Us',
                                    'htmlContent'     => '<h1>About Us</h1><p>Welcome.</p>',
                                    'metaTitle'       => 'About Us',
                                    'metaKeywords'    => 'about,us,company',
                                    'metaDescription' => 'Learn more about our company.',
                                    'locale'          => 'en',
                                    'translations'    => [
                                        ['locale' => 'en', 'url_key' => 'about-us', 'page_title' => 'About Us', 'html_content' => '<h1>About Us</h1><p>Welcome.</p>', 'meta_title' => 'About Us', 'meta_keywords' => 'about,us,company', 'meta_description' => 'Learn more about our company.'],
                                    ],
                                    'channels'        => [['id' => 1, 'code' => 'default', 'name' => 'Default']],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/cms/pages/{id}',
            input: AdminCmsPageUpdateInput::class,
            provider: AdminCmsPageWriteProvider::class,
            processor: AdminCmsPageProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin CMS'],
                summary: 'Update a CMS page (locale-nested)',
                description: 'Mirrors Bagisto admin CMS → Pages → Edit. Validation is LOCALE-NESTED: `<locale>.url_key`, `<locale>.page_title`, `<locale>.html_content` are required. Top-level: `channels` (required), `locale` (required — names which locale block is being updated). url_key uniqueness excludes the current page.',
                parameters: [
                    new Model\Parameter('id', 'path', 'CMS page ID.', true, schema: ['type' => 'integer', 'example' => 7]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['locale', 'channels'],
                                'properties' => [
                                    'locale'   => ['type' => 'string', 'example' => 'en'],
                                    'channels' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'en'       => [
                                        'type'    => 'object',
                                        'example' => [
                                            'url_key'          => 'about-us',
                                            'page_title'       => 'About Us (Updated)',
                                            'html_content'     => '<h1>About Us</h1><p>Welcome back.</p>',
                                            'meta_title'       => 'About Us',
                                            'meta_keywords'    => 'about,us,company',
                                            'meta_description' => 'Updated description.',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Page updated.'),
                    '404' => new Model\Response(description: 'Page not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/cms/pages/{id}',
            provider: AdminCmsPageWriteProvider::class,
            processor: AdminCmsPageProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin CMS'],
                summary: 'Delete a CMS page',
                parameters: [
                    new Model\Parameter('id', 'path', 'CMS page ID.', true, schema: ['type' => 'integer', 'example' => 7]),
                ],
                responses: [
                    '204' => new Model\Response(description: 'Page deleted.'),
                    '404' => new Model\Response(description: 'Page not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/cms/pages/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminCmsPageItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin CMS'],
                summary: 'CMS page detail with all translations + channels',
                parameters: [
                    new Model\Parameter('id', 'path', 'CMS page ID.', true, schema: ['type' => 'integer', 'example' => 7]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Single CMS page with translations and channels inlined.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'              => 7,
                                    'urlKey'          => 'about-us',
                                    'pageTitle'       => 'About Us',
                                    'htmlContent'     => '<h1>About Us</h1>',
                                    'metaTitle'       => 'About Us',
                                    'metaKeywords'    => 'about,us',
                                    'metaDescription' => 'About us page.',
                                    'locale'          => 'en',
                                    'createdAt'       => '2026-01-12T08:15:00+00:00',
                                    'updatedAt'       => '2026-04-30T14:20:09+00:00',
                                    'translations'    => [
                                        ['locale' => 'en', 'url_key' => 'about-us', 'page_title' => 'About Us', 'html_content' => '<h1>About Us</h1>', 'meta_title' => 'About Us', 'meta_keywords' => 'about,us', 'meta_description' => 'About us page.'],
                                    ],
                                    'channels'        => [['id' => 1, 'code' => 'default', 'name' => 'Default']],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Page not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/cms/pages',
            provider: AdminCmsPageCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin CMS'],
                summary: 'List CMS pages (datagrid parity)',
                description: 'Paginated, filterable, sortable CMS pages list mirroring the admin CMS → Pages datagrid.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('id', 'query', 'Filter by CMS page ID.', false, schema: ['type' => 'integer', 'example' => 7]),
                    new Model\Parameter('page_title', 'query', 'Partial page title match.', false, schema: ['type' => 'string', 'example' => 'About']),
                    new Model\Parameter('url_key', 'query', 'Partial url_key match.', false, schema: ['type' => 'string', 'example' => 'about']),
                    new Model\Parameter('channel', 'query', 'Filter by channel ID.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('locale', 'query', 'Locale code for translation resolution.', false, schema: ['type' => 'string', 'example' => 'en']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'page_title', 'url_key', 'created_at'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of CMS page rows in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'        => 7,
                                            'urlKey'    => 'about-us',
                                            'pageTitle' => 'About Us',
                                            'channel'   => 'default',
                                            'locale'    => 'en',
                                            'createdAt' => '2026-01-12T08:15:00+00:00',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 10,
                                        'lastPage'    => 3,
                                        'total'       => 24,
                                        'from'        => 1,
                                        'to'          => 10,
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
            provider: AdminCmsPageCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'id'         => ['type' => 'Int'],
                'page_title' => ['type' => 'String'],
                'url_key'    => ['type' => 'String'],
                'channel'    => ['type' => 'Int'],
                'locale'     => ['type' => 'String'],
                'sort'       => ['type' => 'String'],
                'order'      => ['type' => 'String'],
            ],
            description: 'Admin CMS pages listing (cursor pagination). Mirrors REST GET /api/admin/cms/pages.',
        ),
        new Query(
            provider: AdminCmsPageItemProvider::class,
            description: 'Admin CMS page detail by id, with translations and channels inlined.',
        ),
        new Mutation(
            name: 'create',
            input: AdminCmsPageCreateInput::class,
            processor: AdminCmsPageProcessor::class,
            description: 'Create a new CMS page. Becomes createAdminCmsPage.',
        ),
        new Mutation(
            name: 'update',
            input: AdminCmsPageUpdateInput::class,
            processor: AdminCmsPageProcessor::class,
            description: 'Update a CMS page. Becomes updateAdminCmsPage. Locale-nested payload.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCmsPageUpdateInput::class,
            processor: AdminCmsPageProcessor::class,
            description: 'Delete a CMS page. Becomes deleteAdminCmsPage.',
        ),
    ],
)]
class AdminCmsPage
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $urlKey = null;

    #[ApiProperty(writable: false)]
    public ?string $pageTitle = null;

    #[ApiProperty(writable: false)]
    public ?string $htmlContent = null;

    #[ApiProperty(writable: false)]
    public ?string $metaTitle = null;

    #[ApiProperty(writable: false)]
    public ?string $metaKeywords = null;

    #[ApiProperty(writable: false)]
    public ?string $metaDescription = null;

    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    #[ApiProperty(writable: false)]
    public ?string $channel = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;

    /** @var array<int, mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    /** @var array<int, mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $channels = null;
}
