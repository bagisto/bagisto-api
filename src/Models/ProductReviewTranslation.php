<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\ProductReviewTranslationOutput;
use Webkul\BagistoApi\Resolver\ProductReviewTranslationQueryResolver;
use Webkul\BagistoApi\State\ProductReviewTranslationProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ProductReviewTranslation',
    operations: [
        new Get(
            uriTemplate: '/products/{productId}/reviews/{reviewId}/translation',
            requirements: [
                'productId' => '\\d+',
                'reviewId' => '\\d+',
            ],
            output: ProductReviewTranslationOutput::class,
            provider: ProductReviewTranslationProvider::class,
            openapi: new Model\Operation(
                tags: ['Product'],
                summary: 'Translate an approved product review',
                description: 'Returns the review comment translated into the current store locale. Requires Magic AI and storefront review translation to be enabled.',
                parameters: [
                    new Model\Parameter('productId', 'path', 'Product ID.', true, schema: ['type' => 'integer']),
                    new Model\Parameter('reviewId', 'path', 'Approved review ID.', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    200 => new Model\Response(description: 'Translated review content.'),
                    400 => new Model\Response(description: 'Review is not approved or translation failed.'),
                    403 => new Model\Response(description: 'Review translation is disabled.'),
                    404 => new Model\Response(description: 'Review not found for this product.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: ProductReviewTranslationQueryResolver::class,
            output: ProductReviewTranslationOutput::class,
            args: [
                'productId' => ['type' => 'Int!'],
                'reviewId' => ['type' => 'Int!'],
            ],
            description: 'Translate an approved product review into the current store locale.',
        ),
    ],
)]
class ProductReviewTranslation {}
