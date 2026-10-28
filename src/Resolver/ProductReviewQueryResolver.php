<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\ProductReview;
use Webkul\BagistoApi\Service\GenericIdNormalizer;
use Webkul\BagistoApi\State\ProductReviewProvider;

/**
 * GraphQL: one storefront review, approved or the caller's own.
 */
class ProductReviewQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected ProductReviewProvider $provider) {}

    public function __invoke(?object $item, array $context): ProductReview
    {
        return $this->provider->findVisible((int) GenericIdNormalizer::extractNumericId($context['args']['id'] ?? ''));
    }
}
