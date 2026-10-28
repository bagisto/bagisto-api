<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\GdprDataOutput;
use Webkul\BagistoApi\Resolver\GdprDataQueryResolver;
use Webkul\BagistoApi\State\GdprDataProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GdprData',
    operations: [
        new Get(
            uriTemplate: '/gdpr-data',
            output: GdprDataOutput::class,
            provider: GdprDataProvider::class,
            openapi: new Operation(
                tags: ['GDPR'],
                summary: 'Export the authenticated customer data',
                description: 'Returns the current customer profile, addresses, and orders. Requires an authenticated customer bearer token and GDPR to be enabled.',
                responses: [
                    200 => new Response(description: 'The authenticated customer\'s export data.'),
                    401 => new Response(description: 'A customer bearer token is required.'),
                    404 => new Response(description: 'GDPR is disabled.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: GdprDataQueryResolver::class,
            output: GdprDataOutput::class,
            args: [],
            description: 'Returns the authenticated customer\'s profile, addresses, and orders when GDPR is enabled.',
        ),
    ],
)]
class GdprData {}
