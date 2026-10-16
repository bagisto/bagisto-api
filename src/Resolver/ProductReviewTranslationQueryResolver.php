<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Dto\ProductReviewTranslationOutput;
use Webkul\BagistoApi\State\ProductReviewTranslationProvider;

class ProductReviewTranslationQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(protected ProductReviewTranslationProvider $provider) {}

    /**
     * Resolve the translation of an approved product review.
     */
    public function __invoke(?object $item, array $context): ProductReviewTranslationOutput
    {
        return $this->provider->provideForProductReview(
            (int) ($context['args']['productId'] ?? 0),
            (int) ($context['args']['reviewId'] ?? 0),
        );
    }
}
