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
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSitemapWriteProvider;

/**
 * Admin Marketing → Sitemaps CRUD (Block F3d).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SitemapController 1:1.
 *
 * REST:
 *   GET    /api/admin/marketing/sitemaps
 *   GET    /api/admin/marketing/sitemaps/{id}
 *   POST   /api/admin/marketing/sitemaps
 *   PUT    /api/admin/marketing/sitemaps/{id}
 *   DELETE /api/admin/marketing/sitemaps/{id}
 *
 * GraphQL:
 *   adminMarketingSitemaps         — cursor listing
 *   adminMarketingSitemap(id:)     — detail
 *   createAdminMarketingSitemap
 *   updateAdminMarketingSitemap
 *   deleteAdminMarketingSitemap
 */
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
                tags: ['Admin Marketing'],
                summary: 'Create a sitemap',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['file_name', 'path'],
                                'properties' => [
                                    'file_name' => ['type' => 'string', 'example' => 'sitemap.xml'],
                                    'path'      => ['type' => 'string', 'example' => '/'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Sitemap created. Use POST /generate to build the XML.'),
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
                tags: ['Admin Marketing'],
                summary: 'Update a sitemap',
                responses: [
                    '200' => new Model\Response(description: 'Sitemap updated.'),
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
                tags: ['Admin Marketing'],
                summary: 'Delete a sitemap (removes the DB row and generated XML files).',
                responses: [
                    '200' => new Model\Response(description: 'Sitemap deleted.'),
                    '404' => new Model\Response(description: 'Sitemap not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/sitemaps/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingSitemapItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'Sitemap detail',
                responses: [
                    '200' => new Model\Response(description: 'Sitemap row with generated_at and generated index/sitemap file paths.'),
                    '404' => new Model\Response(description: 'Sitemap not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/sitemaps',
            provider: AdminMarketingSitemapCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'List sitemaps',
                description: 'Filters: file_name (LIKE). Sort: id (default desc), file_name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('file_name', 'query', 'Partial file_name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'file_name']]),
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
            provider: AdminMarketingSitemapCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'file_name' => ['type' => 'String'],
                'sort'      => ['type' => 'String'],
                'order'     => ['type' => 'String'],
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
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $fileName = null;

    #[ApiProperty(writable: false)]
    public ?string $path = null;

    #[ApiProperty(writable: false, description: 'ISO8601 timestamp of the most recent successful generate, or null.')]
    public ?string $generatedAt = null;

    #[ApiProperty(writable: false, description: 'Detail-only: path of the generated sitemap index file under the public disk.')]
    public ?string $indexFile = null;

    #[ApiProperty(writable: false, description: 'Detail-only: list of generated child sitemap file paths under the public disk.')]
    public ?array $generatedSitemaps = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;
}
