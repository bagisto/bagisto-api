<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleMassDeleteProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCatalogRuleMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/catalog-rules/mass-delete',
            input: AdminMarketingCatalogRuleMassDeleteInput::class,
            processor: AdminMarketingCatalogRuleMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Mass delete catalog rules',
                description: 'Deletes a batch of catalog rules. Non-existent IDs are silently skipped.',
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
                        description: 'Catalog rules deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [12, 18],
                                    'skipped' => [],
                                    'message' => 'Catalog rules deleted.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Empty indices.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminMarketingCatalogRuleMassDeleteInput::class,
            processor: AdminMarketingCatalogRuleMassDeleteProcessor::class,
            description: 'Mass-delete a batch of catalog rules. Becomes createAdminMarketingCatalogRuleMassDelete.',
        ),
    ],
)]
class AdminMarketingCatalogRuleMassDelete
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The ids of the catalog rules that were deleted.
     *
     * @var int[]|null
     */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    /**
     * The ids of the catalog rules that were not found.
     *
     * @var int[]|null IDs that did not exist and were skipped.
     */
    #[ApiProperty(writable: false)]
    public ?array $skipped = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
