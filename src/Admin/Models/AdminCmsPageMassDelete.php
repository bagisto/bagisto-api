<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminCmsPageMassDeleteProcessor;

/**
 * One-operation resource for mass-deleting CMS pages.
 *
 * REST:
 *   POST /api/admin/cms/pages/mass-delete
 *     Body: { "indices": [12, 18] }
 *     200:  { "deleted": [12, 18], "message": "..." }
 *
 * GraphQL:
 *   createAdminCmsPageMassDelete
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCmsPageMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/cms/pages/mass-delete',
            input: AdminCmsPageMassDeleteInput::class,
            processor: AdminCmsPageMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin CMS'],
                summary: 'Mass delete CMS pages',
                description: 'Deletes a batch of CMS pages. Non-existent IDs are silently skipped (mirrors monolith).',
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
                        description: 'CMS pages deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'deleted' => [12, 18],
                                    'message' => 'CMS pages deleted successfully.',
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
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'create',
            input: AdminCmsPageMassDeleteInput::class,
            processor: AdminCmsPageMassDeleteProcessor::class,
            description: 'Mass-delete a batch of CMS pages. Becomes createAdminCmsPageMassDelete.',
        ),
    ],
)]
class AdminCmsPageMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $deleted = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
