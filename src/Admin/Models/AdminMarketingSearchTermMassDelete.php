<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchTermMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchTermMassDeleteProcessor;

/**
 * One-operation resource for mass-deleting search terms.
 *
 * REST:
 *   POST /api/admin/marketing/search-terms/mass-delete
 *     Body: { "indices": [12, 18] }
 *     200:  { "deleted": [12, 18], "message": "..." }
 *
 * GraphQL:
 *   createAdminMarketingSearchTermMassDelete
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingSearchTermMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/search-terms/mass-delete',
            input: AdminMarketingSearchTermMassDeleteInput::class,
            processor: AdminMarketingSearchTermMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Mass delete search terms',
                description: 'Deletes a batch of search terms. Non-existent IDs are silently skipped (mirrors monolith).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['indices'],
                                'properties' => [
                                    'indices' => [
                                        'type'    => 'array',
                                        'items'   => ['type' => 'integer'],
                                        'example' => [12, 18],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Search terms deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [12, 18],
                                    'skipped' => [],
                                    'message' => 'Search terms deleted.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Validation failed (e.g. empty indices).',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'create',
            input: AdminMarketingSearchTermMassDeleteInput::class,
            processor: AdminMarketingSearchTermMassDeleteProcessor::class,
            description: 'Mass-delete a batch of search terms. Becomes createAdminMarketingSearchTermMassDelete.',
        ),
    ],
)]
class AdminMarketingSearchTermMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
