<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingUrlRewriteMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingUrlRewriteMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingUrlRewriteMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/url-rewrites/mass-delete',
            input: AdminMarketingUrlRewriteMassDeleteInput::class,
            processor: AdminMarketingUrlRewriteMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Search & SEO'],
                summary: 'Mass delete URL rewrites',
                description: 'Deletes a batch of URL rewrites. Non-existent IDs are silently skipped (mirrors monolith).',
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
                        description: 'URL rewrites deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [12, 18],
                                    'skipped' => [],
                                    'message' => 'URL rewrites deleted.',
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
            input: AdminMarketingUrlRewriteMassDeleteInput::class,
            processor: AdminMarketingUrlRewriteMassDeleteProcessor::class,
            description: 'Mass-delete a batch of URL rewrites. Becomes createAdminMarketingUrlRewriteMassDelete.',
        ),
    ],
)]
class AdminMarketingUrlRewriteMassDelete
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The ids of the URL rewrites that were deleted.
     *
     * @var int[]|null
     */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
