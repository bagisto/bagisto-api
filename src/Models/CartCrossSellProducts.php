<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\ProductDetail\ProductSummaryDto;
use Webkul\BagistoApi\State\CartCrossSellProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CartCrossSell',
    operations: [
        new GetCollection(
            uriTemplate: '/cart/cross-sells',
            output: ProductSummaryDto::class,
            provider: CartCrossSellProvider::class,
            openapi: new Operation(
                tags: ['Cart'],
                summary: 'List cross-sell products for the current cart',
                description: 'Returns the configured cross-sell products for items in the cart identified by the Bearer token. Products already in the cart are excluded.',
                responses: [
                    200 => new Response(description: 'The cart cross-sell product summaries.'),
                    401 => new Response(description: 'A bearer cart token is required.'),
                    404 => new Response(description: 'The bearer cart token is invalid.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            output: ProductSummaryDto::class,
            provider: CartCrossSellProvider::class,
            description: 'Returns cross-sell products for the cart identified by the Bearer token.',
        ),
    ],
)]
class CartCrossSellProducts {}
