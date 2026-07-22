<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminReturnReasonProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReturnReason',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/requests/resolution-reasons',
            provider: AdminReturnReasonProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'List active return reasons for a resolution type',
                description: 'Reasons available when creating a return, filtered by `?resolution_type=return|cancel_items`.',
                parameters: [
                    new Model\Parameter('resolution_type', 'query', 'return | cancel_items', true, schema: ['type' => 'string', 'enum' => ['return', 'cancel_items']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Active reasons for the resolution type.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    ['id' => 2, 'title' => 'Damaged product', 'position' => 1],
                                    ['id' => 3, 'title' => 'Wrong item delivered', 'position' => 2],
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
            provider: AdminReturnReasonProvider::class,
            paginationType: 'cursor',
            args: [
                'resolutionType' => ['type' => 'String!', 'description' => 'return | cancel_items'],
            ],
        ),
    ],
)]
class AdminReturnReason
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $title = null;

    public ?int $position = null;
}
