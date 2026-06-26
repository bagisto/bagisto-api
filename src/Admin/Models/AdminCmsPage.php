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
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageDetailDto;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageListDto;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminCmsPageCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCmsPageExportProvider;
use Webkul\BagistoApi\Admin\State\AdminCmsPageItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCmsPageProcessor;
use Webkul\BagistoApi\Admin\State\AdminCmsPageWriteProvider;

/**
 * Admin CMS → Pages endpoints (CMS Phase 1 read-only + CMS Phase 2 CRUD).
 *
 * Standalone Eloquent model on `cms_pages` following the shop CustomerOrder
 * pattern: REST operations map to output DTOs (AdminCmsPageListDto /
 * AdminCmsPageDetailDto) so the REST payload is byte-identical to the original,
 * while GraphQL operations return this Eloquent model so the `translations` and
 * `channels` relations are field-selectable
 * (e.g. `translations { pageTitle urlKey } channels { id code name }`).
 *
 * Providers gate on GraphQL context to return the model for GraphQL and the
 * DTO for REST.
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
            output: AdminCmsPageDetailDto::class,
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
            output: AdminCmsPageDetailDto::class,
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
            output: AdminCmsPageDetailDto::class,
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
            output: AdminCmsPageListDto::class,
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
        new Get(
            uriTemplate: '/cms/pages/export',
            provider: AdminCmsPageExportProvider::class,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin CMS'],
                summary: 'Export CMS pages as CSV',
                description: 'Downloads the CMS Pages datagrid as a CSV file (`text/csv` attachment) — the same data the admin CMS → Pages "view" listing shows (ID, Page Title, URL Key, Channel, Locale). Honours the same filters as the listing (`id`, `page_title`, `url_key`, `channel`, `locale`). The response is a binary download, not JSON.',
                parameters: [
                    new Model\Parameter('format', 'query', 'Export format. Currently only `csv` is supported.', false, schema: ['type' => 'string', 'enum' => ['csv'], 'default' => 'csv']),
                    new Model\Parameter('id', 'query', 'Filter by CMS page ID.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('page_title', 'query', 'Partial page title match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('url_key', 'query', 'Partial url_key match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('channel', 'query', 'Filter by channel ID.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('locale', 'query', 'Locale code for translation resolution.', false, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The CMS pages CSV file is downloaded (text/csv attachment).',
                        content: new \ArrayObject([
                            'text/csv' => [
                                'schema' => ['type' => 'string', 'format' => 'binary'],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks permission.'),
                    '422' => new Model\Response(description: 'Unsupported format (only csv).'),
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
            description: 'Admin CMS pages listing (cursor pagination). Mirrors REST GET /api/admin/cms/pages. The translations and channels relations are field-selectable.',
        ),
        new Query(
            provider: AdminCmsPageItemProvider::class,
            description: 'Admin CMS page detail by id. The translations and channels relations are field-selectable.',
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
class AdminCmsPage extends EloquentModel
{
    protected $table = 'cms_pages';

    protected $casts = [
        'id'         => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'url_key',
        'page_title',
        'html_content',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'locale',
        'channel',
        'preview_url',
        'message',
    ];

    /** Transient action message (e.g. delete confirmation); not a DB column. */
    public ?string $actionMessage = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getMessageAttribute(): ?string
    {
        return $this->actionMessage;
    }

    #[ApiProperty(writable: false)]
    public function translations(): HasMany
    {
        return $this->hasMany(AdminCmsPageTranslation::class, 'cms_page_id');
    }

    #[ApiProperty(writable: false)]
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(AdminCmsPageChannel::class, 'cms_page_channels', 'cms_page_id', 'channel_id');
    }

    #[ApiProperty(writable: false)]
    public function getUrlKeyAttribute(): ?string
    {
        return $this->primaryTranslation()?->url_key;
    }

    #[ApiProperty(writable: false)]
    public function getPageTitleAttribute(): ?string
    {
        return $this->primaryTranslation()?->page_title;
    }

    #[ApiProperty(writable: false)]
    public function getHtmlContentAttribute(): ?string
    {
        return $this->primaryTranslation()?->html_content;
    }

    #[ApiProperty(writable: false)]
    public function getMetaTitleAttribute(): ?string
    {
        return $this->primaryTranslation()?->meta_title;
    }

    #[ApiProperty(writable: false)]
    public function getMetaKeywordsAttribute(): ?string
    {
        return $this->primaryTranslation()?->meta_keywords;
    }

    #[ApiProperty(writable: false)]
    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->primaryTranslation()?->meta_description;
    }

    #[ApiProperty(writable: false)]
    public function getLayoutAttribute($value): ?string
    {
        return $value;
    }

    #[ApiProperty(writable: false)]
    public function getLocaleAttribute(): ?string
    {
        return $this->primaryTranslation()?->locale;
    }

    #[ApiProperty(writable: false)]
    public function getChannelAttribute(): ?string
    {
        $codes = $this->channels->pluck('code')->filter()->values()->all();

        return $codes ? implode(',', $codes) : null;
    }

    #[ApiProperty(writable: false)]
    public function getPreviewUrlAttribute(): ?string
    {
        $urlKey = $this->getUrlKeyAttribute();

        if (! $urlKey) {
            return null;
        }

        try {
            return route('shop.cms.page', $urlKey);
        } catch (\Throwable) {
            return rtrim((string) config('app.url'), '/').'/page/'.$urlKey;
        }
    }

    #[ApiProperty(writable: false)]
    public function getCreatedAt(): ?string
    {
        return $this->created_at?->toIso8601String();
    }

    #[ApiProperty(writable: false)]
    public function getUpdatedAt(): ?string
    {
        return $this->updated_at?->toIso8601String();
    }

    protected function primaryTranslation(): ?AdminCmsPageTranslation
    {
        $translations = $this->translations;

        return $translations->firstWhere('locale', app()->getLocale())
            ?? $translations->first();
    }
}
