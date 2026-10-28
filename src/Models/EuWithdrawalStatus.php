<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\EuWithdrawalStatusQueryResolver;
use Webkul\BagistoApi\State\FeatureStatusProvider;

/**
 * Whether the EU right of withdrawal is enabled on the current channel.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'EuWithdrawalStatus',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/eu-withdrawal-status',
            provider: FeatureStatusProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['EU Withdrawal'],
                summary: 'Check whether EU withdrawal is enabled',
                description: 'Returns whether customers can file an EU withdrawal on the current channel.',
                responses: [
                    '200' => new Response(
                        description: 'The EU withdrawal setting of the current channel.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 'default',
                                        'channel' => 'default',
                                        'enabled' => false,
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
        new Query(
            resolver: EuWithdrawalStatusQueryResolver::class,
            args: [],
        ),
    ],
)]
class EuWithdrawalStatus
{
    #[ApiProperty(identifier: true)]
    public ?string $id = null;

    public ?string $channel = null;

    public ?bool $enabled = null;
}
