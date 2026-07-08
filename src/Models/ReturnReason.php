<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
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
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer Return'],
                summary: 'List active return reasons for a resolution type',
                description: 'Reasons a customer can pick when raising a return, filtered by `?resolution_type=return|cancel_items`. Use these ids as `rmaReasonId` when creating a return.',
                parameters: [
                    new \ApiPlatform\OpenApi\Model\Parameter('resolution_type', 'query', 'return | cancel_items', true, schema: ['type' => 'string', 'enum' => ['return', 'cancel_items']]),
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
