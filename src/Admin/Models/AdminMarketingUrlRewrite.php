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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingUrlRewriteCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingUrlRewriteUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteWriteProvider;

/**
 * Admin Marketing → URL Rewrites endpoints (Block F3a).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\URLRewriteController 1:1.
 *
 * REST:
 *   GET    /api/admin/marketing/url-rewrites
 *   GET    /api/admin/marketing/url-rewrites/{id}
 *   POST   /api/admin/marketing/url-rewrites
 *   PUT    /api/admin/marketing/url-rewrites/{id}
 *   DELETE /api/admin/marketing/url-rewrites/{id}
 *
 * GraphQL:
 *   adminMarketingUrlRewrites      — cursor listing
 *   adminMarketingUrlRewrite(id:)  — detail
 *   createAdminMarketingUrlRewrite
 *   updateAdminMarketingUrlRewrite
 *   deleteAdminMarketingUrlRewrite
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingUrlRewrite',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/url-rewrites',
            input: AdminMarketingUrlRewriteCreateInput::class,
            processor: AdminMarketingUrlRewriteProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Create a URL rewrite',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['entity_type', 'request_path', 'target_path', 'redirect_type', 'locale'],
                                'properties' => [
                                    'entity_type'   => ['type' => 'string', 'enum' => ['product', 'category', 'cms_page'], 'example' => 'product'],
                                    'request_path'  => ['type' => 'string', 'example' => 'old-path'],
                                    'target_path'   => ['type' => 'string', 'example' => 'new-path'],
                                    'redirect_type' => ['type' => 'string', 'enum' => ['301', '302'], 'example' => '301'],
                                    'locale'        => ['type' => 'string', 'example' => 'en'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'URL rewrite created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/url-rewrites/{id}',
            input: AdminMarketingUrlRewriteUpdateInput::class,
            provider: AdminMarketingUrlRewriteWriteProvider::class,
            processor: AdminMarketingUrlRewriteProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Update a URL rewrite',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'entity_type'   => ['type' => 'string', 'enum' => ['product', 'category', 'cms_page'], 'example' => 'product'],
                                    'request_path'  => ['type' => 'string', 'example' => 'old-path'],
                                    'target_path'   => ['type' => 'string', 'example' => 'new-path'],
                                    'redirect_type' => ['type' => 'string', 'enum' => ['301', '302'], 'example' => '301'],
                                    'locale'        => ['type' => 'string', 'example' => 'en'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'URL rewrite updated.'),
                    '404' => new Model\Response(description: 'URL rewrite not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/url-rewrites/{id}',
            provider: AdminMarketingUrlRewriteWriteProvider::class,
            processor: AdminMarketingUrlRewriteProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Delete a URL rewrite',
                responses: [
                    '200' => new Model\Response(description: 'URL rewrite deleted.'),
                    '404' => new Model\Response(description: 'URL rewrite not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/url-rewrites/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingUrlRewriteItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'URL rewrite detail',
                responses: [
                    '200' => new Model\Response(description: 'Single URL rewrite.'),
                    '404' => new Model\Response(description: 'URL rewrite not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/url-rewrites',
            provider: AdminMarketingUrlRewriteCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'List URL rewrites',
                description: 'Filters: entity_type, request_path (LIKE), redirect_type, locale. Sort: id (default desc), entity_type, locale, redirect_type.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('entity_type', 'query', 'Entity type filter (product|category|cms_page).', false, schema: ['type' => 'string']),
                    new Model\Parameter('request_path', 'query', 'Partial match on request_path.', false, schema: ['type' => 'string']),
                    new Model\Parameter('redirect_type', 'query', 'Redirect type filter (301|302).', false, schema: ['type' => 'string']),
                    new Model\Parameter('locale', 'query', 'Locale filter.', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'entity_type', 'locale', 'redirect_type']]),
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
            provider: AdminMarketingUrlRewriteCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'entity_type'   => ['type' => 'String'],
                'request_path'  => ['type' => 'String'],
                'redirect_type' => ['type' => 'String'],
                'locale'        => ['type' => 'String'],
                'sort'          => ['type' => 'String'],
                'order'         => ['type' => 'String'],
            ],
            description: 'Admin URL rewrites listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingUrlRewriteItemProvider::class,
            description: 'Admin URL rewrite detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingUrlRewriteCreateInput::class,
            processor: AdminMarketingUrlRewriteProcessor::class,
            description: 'Create a URL rewrite. Becomes createAdminMarketingUrlRewrite.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingUrlRewriteUpdateInput::class,
            processor: AdminMarketingUrlRewriteProcessor::class,
            description: 'Update a URL rewrite. Becomes updateAdminMarketingUrlRewrite.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingUrlRewriteUpdateInput::class,
            processor: AdminMarketingUrlRewriteProcessor::class,
            description: 'Delete a URL rewrite. Becomes deleteAdminMarketingUrlRewrite.',
        ),
    ],
)]
class AdminMarketingUrlRewrite
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $entity_type = null;

    #[ApiProperty(writable: false)]
    public ?string $request_path = null;

    #[ApiProperty(writable: false)]
    public ?string $target_path = null;

    #[ApiProperty(writable: false)]
    public ?string $redirect_type = null;

    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
