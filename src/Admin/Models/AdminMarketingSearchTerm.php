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
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchTermRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchTermUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermWriteProvider;

/**
 * Admin Marketing → Search Terms (Block F3b).
 *
 * REST:
 *   GET    /api/admin/marketing/search-terms
 *   GET    /api/admin/marketing/search-terms/{id}
 *   PUT    /api/admin/marketing/search-terms/{id}
 *   DELETE /api/admin/marketing/search-terms/{id}
 *
 * GraphQL: adminMarketingSearchTerms, adminMarketingSearchTerm,
 *          updateAdminMarketingSearchTerm, deleteAdminMarketingSearchTerm
 *
 * Search terms are auto-recorded by storefront searches; admin only edits/deletes.
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\SearchSEO\SearchTermController.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingSearchTerm',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Put(
            uriTemplate: '/marketing/search-terms/{id}',
            input: AdminMarketingSearchTermUpdateInput::class,
            output: AdminMarketingSearchTermRestDto::class,
            provider: AdminMarketingSearchTermWriteProvider::class,
            processor: AdminMarketingSearchTermProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Update a search term',
                description: 'Admin can edit the term text and optional redirect URL. Counts (uses/results) are not editable.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Search term ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['term'],
                                'properties' => [
                                    'term'         => ['type' => 'string', 'example' => 'red shirt'],
                                    'redirect_url' => ['type' => 'string', 'nullable' => true, 'example' => 'https://example.com/shirts'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Search term updated; returns the updated detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'          => 106,
                                    'term'        => 'Coastal Breeze QA',
                                    'results'     => 1,
                                    'uses'        => 3,
                                    'redirectUrl' => 'https://example.com/qa',
                                    'channel'     => ['id' => 1, 'code' => 'default', 'name' => 'Default'],
                                    'locale'      => 'en',
                                    'createdAt'   => '2026-06-03T13:14:05+05:30',
                                    'updatedAt'   => '2026-06-17T12:14:07+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Search term not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/search-terms/{id}',
            provider: AdminMarketingSearchTermWriteProvider::class,
            processor: AdminMarketingSearchTermProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Delete a search term',
                parameters: [
                    new Model\Parameter('id', 'path', 'Search term ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Search term deleted.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/search-terms/{id}',
            provider: AdminMarketingSearchTermItemProvider::class,
            output: AdminMarketingSearchTermRestDto::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Search term detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Search term ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Single search term.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'          => 106,
                                    'term'        => 'Coastal Breeze QA',
                                    'results'     => 1,
                                    'uses'        => 3,
                                    'redirectUrl' => 'https://example.com/qa',
                                    'channel'     => ['id' => 1, 'code' => 'default', 'name' => 'Default'],
                                    'locale'      => 'en',
                                    'createdAt'   => '2026-06-03T13:14:05+05:30',
                                    'updatedAt'   => '2026-06-17T12:14:07+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Search term not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/search-terms',
            provider: AdminMarketingSearchTermCollectionProvider::class,
            output: AdminMarketingSearchTermRestDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'List search terms',
                description: 'Paginated, filterable, sortable list. Sort by uses desc for popular terms. Returns { data, meta } envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('term', 'query', 'Term LIKE filter', false, schema: ['type' => 'string']),
                    new Model\Parameter('channel_id', 'query', 'Filter by channel id', false, schema: ['type' => 'integer']),
                    new Model\Parameter('locale', 'query', 'Filter by locale code', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'term', 'uses', 'results']]),
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
                                            'id'          => 106,
                                            'term'        => 'Coastal Breeze QA',
                                            'results'     => 1,
                                            'uses'        => 3,
                                            'redirectUrl' => 'https://example.com/qa',
                                            'channel'     => ['id' => 1, 'code' => 'default', 'name' => 'Default'],
                                            'locale'      => 'en',
                                            'createdAt'   => '2026-06-03T13:14:05+05:30',
                                            'updatedAt'   => '2026-06-17T12:14:07+05:30',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 10,
                                        'lastPage'    => 11,
                                        'total'       => 103,
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
            provider: AdminMarketingSearchTermCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'term'       => ['type' => 'String'],
                'channel_id' => ['type' => 'Int'],
                'locale'     => ['type' => 'String'],
                'sort'       => ['type' => 'String'],
                'order'      => ['type' => 'String'],
            ],
            description: 'Admin search terms listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingSearchTermItemProvider::class,
            description: 'Admin search term detail by id.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingSearchTermUpdateInput::class,
            processor: AdminMarketingSearchTermProcessor::class,
            description: 'Update a search term. Becomes updateAdminMarketingSearchTerm.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingSearchTermUpdateInput::class,
            processor: AdminMarketingSearchTermProcessor::class,
            description: 'Delete a search term. Becomes deleteAdminMarketingSearchTerm.',
        ),
    ],
)]
class AdminMarketingSearchTerm extends EloquentModel
{
    /** @var string */
    protected $table = 'search_terms';

    /** @var array */
    protected $casts = [
        'id'         => 'int',
        'results'    => 'int',
        'uses'       => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    /**
     * Channel this term was searched in (GraphQL to-one object —
     * `channel { id _id code name }`). The belongsTo on `channel_id` consumes
     * that FK column, replacing the old channelId / channelName scalars.
     */
    #[ApiProperty(writable: false)]
    public function channel(): BelongsTo
    {
        return $this->belongsTo(AdminMarketingChannelRef::class, 'channel_id');
    }
}
