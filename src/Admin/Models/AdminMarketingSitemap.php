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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSitemapCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSitemapUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingSitemap',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/sitemaps',
            input: AdminMarketingSitemapCreateInput::class,
            processor: AdminMarketingSitemapProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Create a sitemap',
                description: 'Creates a sitemap definition. `file_name` must end in .xml, `path` must start and end with a slash, and `channels` lists existing channel IDs. The XML is built by the generate endpoint, not here.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['file_name', 'path', 'channels'],

                                'properties' => [
                                    'file_name' => [
                                        'type' => 'string',
                                        'example' => 'sitemap.xml',
                                    ],

                                    'path' => [
                                        'type' => 'string',
                                        'example' => '/',
                                    ],

                                    'channels' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'example' => [1],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Sitemap created. Use POST /generate to build the XML.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'fileName' => 'sitemap.xml',
                                    'path' => '/',
                                    'channels' => [1],
                                    'urls' => ['https://example.com/storage/sitemaps/default/sitemap-1-1.xml'],
                                    'generatedAt' => null,
                                    'generatedFiles' => [],
                                    'indexFile' => null,
                                    'generatedSitemaps' => [],
                                    'createdAt' => '2026-06-20T10:00:00+05:30',
                                    'updatedAt' => '2026-06-20T10:00:00+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/sitemaps/{id}',
            input: AdminMarketingSitemapUpdateInput::class,
            provider: AdminMarketingSitemapWriteProvider::class,
            processor: AdminMarketingSitemapProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Update a sitemap',
                description: 'Updates the sitemap definition; `file_name`, `path` and `channels` are validated as on create. An unknown ID is a 404.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',

                                'properties' => [
                                    'file_name' => [
                                        'type' => 'string',
                                        'example' => 'sitemap.xml',
                                    ],

                                    'path' => [
                                        'type' => 'string',
                                        'example' => '/',
                                    ],

                                    'channels' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'example' => [1],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Sitemap updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'fileName' => 'sitemap.xml',
                                    'path' => '/',
                                    'channels' => [1],
                                    'urls' => ['https://example.com/storage/sitemaps/default/sitemap-1-1.xml'],
                                    'generatedAt' => '2026-06-23T13:00:00+05:30',

                                    'generatedFiles' => [
                                        [
                                            'channelId' => 1,
                                            'channelCode' => 'default',
                                            'hostname' => 'https://example.com',
                                            'index' => 'sitemaps/default/sitemap-1-1.xml',
                                            'sitemaps' => ['sitemaps/default/sitemap-1-1-1.xml'],
                                        ],
                                    ],

                                    'indexFile' => null,
                                    'generatedSitemaps' => [],
                                    'createdAt' => '2026-06-20T10:00:00+05:30',
                                    'updatedAt' => '2026-06-23T13:05:00+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Sitemap not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/sitemaps/{id}',
            provider: AdminMarketingSitemapWriteProvider::class,
            processor: AdminMarketingSitemapProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Delete a sitemap (removes the DB row and generated XML files).',
                description: 'Deletes the sitemap definition and the XML files generated for it. An unknown ID is a 404.',
                responses: [
                    '200' => new Model\Response(
                        description: 'Sitemap deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Sitemap deleted.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Sitemap not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/sitemaps/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingSitemapItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Sitemap detail',
                description: 'One sitemap with its channels, its public URLs and, once it has been generated, `generatedAt`, the index file and the sitemap files written per channel. Needs the `marketing.search_seo.sitemaps` permission.',
                responses: [
                    '200' => new Model\Response(
                        description: 'Sitemap row with generated_at and generated index/sitemap file paths.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'fileName' => 'sitemap.xml',
                                    'path' => '/',
                                    'channels' => [1],
                                    'urls' => ['https://example.com/storage/sitemaps/default/sitemap-1-1.xml'],
                                    'generatedAt' => '2026-06-23T13:00:00+05:30',

                                    'generatedFiles' => [
                                        [
                                            'channelId' => 1,
                                            'channelCode' => 'default',
                                            'hostname' => 'https://example.com',
                                            'index' => 'sitemaps/default/sitemap-1-1.xml',
                                            'sitemaps' => ['sitemaps/default/sitemap-1-1-1.xml'],
                                        ],
                                    ],

                                    'indexFile' => null,
                                    'generatedSitemaps' => [],
                                    'createdAt' => '2026-06-20T10:00:00+05:30',
                                    'updatedAt' => '2026-06-23T13:05:00+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Sitemap not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/sitemaps',
            provider: AdminMarketingSitemapCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'List sitemaps',
                description: 'Filters: file_name (LIKE), channel_id (exact). Sort: id (default desc), file_name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: [
                        'type' => 'integer',
                        'example' => 1,
                    ]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: [
                        'type' => 'integer',
                        'example' => 10,
                    ]),
                    new Model\Parameter('file_name', 'query', 'Partial file_name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('channel_id', 'query', 'Only sitemaps covering this channel.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: [
                        'type' => 'string',
                        'enum' => ['id', 'file_name'],
                    ]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: [
                        'type' => 'string',
                        'enum' => ['asc', 'desc'],
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list in the { data, meta } envelope. generatedFiles / indexFile / generatedSitemaps are detail-only and null on list rows.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 1,
                                            'fileName' => 'sitemap.xml',
                                            'path' => '/',
                                            'channels' => [1],
                                            'urls' => ['https://example.com/storage/sitemaps/default/sitemap-1-1.xml'],
                                            'generatedAt' => null,
                                            'generatedFiles' => null,
                                            'indexFile' => null,
                                            'generatedSitemaps' => null,
                                            'createdAt' => '2026-06-20T10:00:00+05:30',
                                            'updatedAt' => '2026-06-20T10:00:00+05:30',
                                        ],
                                    ],

                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 1,
                                        'total' => 1,
                                        'from' => 1,
                                        'to' => 1,
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
            provider: AdminMarketingSitemapCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'file_name' => ['type' => 'String'],
                'channel_id' => ['type' => 'Int'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin marketing sitemaps listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingSitemapItemProvider::class,
            description: 'Admin marketing sitemap detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingSitemapCreateInput::class,
            processor: AdminMarketingSitemapProcessor::class,
            description: 'Create a sitemap. Becomes createAdminMarketingSitemap.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingSitemapUpdateInput::class,
            processor: AdminMarketingSitemapProcessor::class,
            description: 'Update a sitemap. Becomes updateAdminMarketingSitemap.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingSitemapUpdateInput::class,
            processor: AdminMarketingSitemapProcessor::class,
            description: 'Delete a sitemap. Becomes deleteAdminMarketingSitemap.',
        ),
    ],
)]
class AdminMarketingSitemap
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The file name.
     */
    #[ApiProperty(writable: false)]
    public ?string $file_name = null;

    /**
     * The path.
     */
    #[ApiProperty(writable: false)]
    public ?string $path = null;

    /**
     * The channels.
     */
    #[ApiProperty(writable: false, description: 'Channel ids the sitemap covers. A sitemap generates one index plus its child files per channel.')]
    public ?array $channels = null;

    /**
     * The urls.
     */
    #[ApiProperty(writable: false, description: 'Public index URL per channel — the link to submit to a search engine.')]
    public ?array $urls = null;

    /**
     * The generated at.
     */
    #[ApiProperty(writable: false, description: 'ISO8601 timestamp of the most recent successful generate, or null.')]
    public ?string $generated_at = null;

    /**
     * The generated files.
     */
    #[ApiProperty(writable: false, description: 'Detail-only: what the last generate wrote, one entry per channel — { channelId, channelCode, hostname, index, sitemaps }.')]
    public ?array $generated_files = null;

    /**
     * The index file.
     */
    #[ApiProperty(writable: false, description: 'Detail-only, legacy: index path of a sitemap generated before generation became channel-aware. Null for anything generated since; read generatedFiles instead.')]
    public ?string $index_file = null;

    /**
     * The generated sitemaps.
     */
    #[ApiProperty(writable: false, description: 'Detail-only, legacy: child file paths of a sitemap generated before generation became channel-aware. Empty for anything generated since; read generatedFiles instead.')]
    public ?array $generated_sitemaps = null;

    /**
     * The created at.
     */
    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
