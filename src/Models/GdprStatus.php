<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\GdprStatusQueryResolver;
use Webkul\BagistoApi\State\FeatureStatusProvider;

/**
 * Whether GDPR data requests are enabled on the current channel.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GdprStatus',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/gdpr-status',
            provider: FeatureStatusProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['GDPR Requests'],
                summary: 'Check whether GDPR requests are enabled',
                description: 'Returns whether customers can raise GDPR data requests on the current channel.',
                responses: [
                    '200' => new Response(
                        description: 'The GDPR setting of the current channel.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 'default',
                                        'channel' => 'default',
                                        'enabled' => true,
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
            resolver: GdprStatusQueryResolver::class,
            args: [],
        ),
    ],
)]
class GdprStatus
{
    #[ApiProperty(identifier: true)]
    public ?string $id = null;

    public ?string $channel = null;

    public ?bool $enabled = null;
}
