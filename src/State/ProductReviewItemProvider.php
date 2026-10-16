<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\ProductReview;

/**
 * Provider for the public single-review lookup; only approved reviews are visible.
 */
class ProductReviewItemProvider implements ProviderInterface
{
    /**
     * Resolve an approved review, or null so the request answers 404.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return ProductReview::query()
            ->where('status', ProductReview::STATUS_APPROVED)
            ->with(['product', 'customer'])
            ->find($uriVariables['id'] ?? null);
    }
}
