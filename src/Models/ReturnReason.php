<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\State\ReturnReasonProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ReturnReason',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/return-reasons',
            provider: ReturnReasonProvider::class,
            openapi: new Operation(
                tags: ['Customer Return'],
                summary: 'List active return reasons for a resolution type',
                description: 'Reasons a customer can pick when raising a return, filtered by `?resolution_type=return|cancel_items`. Use these ids as `rmaReasonId` when creating a return.',
                parameters: [
                    new Parameter('resolution_type', 'query', 'return | cancel_items', true, schema: ['type' => 'string', 'enum' => ['return', 'cancel_items']]),
                ],
                responses: [
                    '200' => new Response(
                        description: 'Active reasons for the resolution type. Use an id as rmaReasonId when creating a return.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    ['id' => 2, 'title' => 'Damaged product', 'position' => 1],
                                    ['id' => 3, 'title' => 'Wrong item delivered', 'position' => 2],
                                    ['id' => 4, 'title' => 'No longer needed', 'position' => 3],
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
            provider: ReturnReasonProvider::class,
            paginationType: 'cursor',
            args: [
                'resolutionType' => ['type' => 'String!', 'description' => 'return | cancel_items'],
            ],
        ),
    ],
)]
class ReturnReason
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $title = null;

    public ?int $position = null;
}
