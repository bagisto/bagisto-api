<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\CheckoutMessageOutput;
use Webkul\BagistoApi\Resolver\CheckoutMessageQueryResolver;
use Webkul\BagistoApi\State\CheckoutMessageProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CheckoutMessage',
    operations: [
        new Get(
            uriTemplate: '/checkout-messages/{orderId}',
            requirements: ['orderId' => '\\d+'],
            output: CheckoutMessageOutput::class,
            provider: CheckoutMessageProvider::class,
            openapi: new Model\Operation(
                tags: ['Checkout'],
                summary: 'Generate the checkout success message for a placed order',
                description: 'Returns the AI-generated checkout success message for an order placed with the bearer token (guest cart token or customer token). Generated on demand, so placing an order never waits on Magic AI. The message is null when the Magic AI checkout-message feature is disabled or generation fails.',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID returned by the checkout.', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    200 => new Model\Response(description: 'The checkout success message, or null.'),
                    401 => new Model\Response(description: 'A bearer token is required.'),
                    404 => new Model\Response(description: 'No order with this ID was placed with the bearer token.'),
                    429 => new Model\Response(description: 'Too many requests.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: CheckoutMessageQueryResolver::class,
            output: CheckoutMessageOutput::class,
            args: [
                'orderId' => ['type' => 'Int!'],
            ],
            description: 'Generate the AI checkout success message for an order placed with the bearer token; null when the feature is disabled or generation fails.',
        ),
    ],
)]
class CheckoutMessage {}
