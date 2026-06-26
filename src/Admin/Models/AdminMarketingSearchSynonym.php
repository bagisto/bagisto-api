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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchSynonymCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchSynonymUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymWriteProvider;

/**
 * Admin Marketing → Search Synonyms endpoints (Block F3c).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SearchSynonymController 1:1.
 *
 * REST:
 *   GET    /api/admin/marketing/search-synonyms
 *   GET    /api/admin/marketing/search-synonyms/{id}
 *   POST   /api/admin/marketing/search-synonyms
 *   PUT    /api/admin/marketing/search-synonyms/{id}
 *   DELETE /api/admin/marketing/search-synonyms/{id}
 *
 * GraphQL:
 *   adminMarketingSearchSynonyms        — cursor listing
 *   adminMarketingSearchSynonym(id:)    — detail
 *   createAdminMarketingSearchSynonym
 *   updateAdminMarketingSearchSynonym
 *   deleteAdminMarketingSearchSynonym
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingSearchSynonym',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/search-synonyms',
            input: AdminMarketingSearchSynonymCreateInput::class,
            processor: AdminMarketingSearchSynonymProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Create a search synonym',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'terms'],
                                'properties' => [
                                    'name'  => ['type' => 'string', 'example' => 'shirt-group'],
                                    'terms' => ['type' => 'string', 'example' => 'shirt,tshirt,tee'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Search synonym created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 19,
                                    'name'      => 'shirt-group',
                                    'terms'     => 'shirt,tshirt,tee',
                                    'createdAt' => '2026-05-28T10:57:59+05:30',
                                    'updatedAt' => '2026-05-28T10:57:59+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/search-synonyms/{id}',
            input: AdminMarketingSearchSynonymUpdateInput::class,
            provider: AdminMarketingSearchSynonymWriteProvider::class,
            processor: AdminMarketingSearchSynonymProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Update a search synonym',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name'  => ['type' => 'string', 'example' => 'shirt-group'],
                                    'terms' => ['type' => 'string', 'example' => 'shirt,tshirt,tee'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Search synonym updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 19,
                                    'name'      => 'shirt-group',
                                    'terms'     => 'shirt,tshirt,tee',
                                    'createdAt' => '2026-05-28T10:57:59+05:30',
                                    'updatedAt' => '2026-05-28T10:57:59+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Search synonym not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/search-synonyms/{id}',
            provider: AdminMarketingSearchSynonymWriteProvider::class,
            processor: AdminMarketingSearchSynonymProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Delete a search synonym',
                responses: [
                    '200' => new Model\Response(
                        description: 'Search synonym deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'message' => 'Search synonym deleted.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Search synonym not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/search-synonyms/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingSearchSynonymItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Search synonym detail',
                responses: [
                    '200' => new Model\Response(
                        description: 'Single search synonym.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'        => 19,
                                    'name'      => 'shirt-group',
                                    'terms'     => 'shirt,tshirt,tee',
                                    'createdAt' => '2026-05-28T10:57:59+05:30',
                                    'updatedAt' => '2026-05-28T10:57:59+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Search synonym not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/search-synonyms',
            provider: AdminMarketingSearchSynonymCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'List search synonyms',
                description: 'Filters: name (LIKE), terms (LIKE). Sort: id (default desc), name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('terms', 'query', 'Partial terms match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'name']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'        => 19,
                                            'name'      => 'shirt-group',
                                            'terms'     => 'shirt,tshirt,tee',
                                            'createdAt' => '2026-05-28T10:57:59+05:30',
                                            'updatedAt' => '2026-05-28T10:57:59+05:30',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 10,
                                        'lastPage'    => 1,
                                        'total'       => 3,
                                        'from'        => 1,
                                        'to'          => 3,
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
            provider: AdminMarketingSearchSynonymCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name'  => ['type' => 'String'],
                'terms' => ['type' => 'String'],
                'sort'  => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin marketing search synonyms listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingSearchSynonymItemProvider::class,
            description: 'Admin marketing search synonym detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingSearchSynonymCreateInput::class,
            processor: AdminMarketingSearchSynonymProcessor::class,
            description: 'Create a search synonym. Becomes createAdminMarketingSearchSynonym.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingSearchSynonymUpdateInput::class,
            processor: AdminMarketingSearchSynonymProcessor::class,
            description: 'Update a search synonym. Becomes updateAdminMarketingSearchSynonym.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingSearchSynonymUpdateInput::class,
            processor: AdminMarketingSearchSynonymProcessor::class,
            description: 'Delete a search synonym. Becomes deleteAdminMarketingSearchSynonym.',
        ),
    ],
)]
class AdminMarketingSearchSynonym
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $terms = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
