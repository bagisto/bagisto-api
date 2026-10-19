<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSearchSynonymMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingSearchSynonymMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingSearchSynonymMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/search-synonyms/mass-delete',
            input: AdminMarketingSearchSynonymMassDeleteInput::class,
            processor: AdminMarketingSearchSynonymMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Mass delete search synonyms',
                description: 'Deletes a batch of search synonyms. Non-existent IDs are silently skipped (mirrors monolith).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['indices'],

                                'properties' => [
                                    'indices' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'example' => [12, 18],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Search synonyms deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [12, 18],
                                    'skipped' => [],
                                    'message' => 'Search synonyms deleted.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure (empty/missing indices).'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminMarketingSearchSynonymMassDeleteInput::class,
            processor: AdminMarketingSearchSynonymMassDeleteProcessor::class,
            description: 'Mass-delete search synonyms. Becomes createAdminMarketingSearchSynonymMassDelete.',
        ),
    ],
)]
class AdminMarketingSearchSynonymMassDelete
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
