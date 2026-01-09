<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\GraphQl\State\Provider\ReadProvider as BaseReadProvider;
use ApiPlatform\Metadata\Operation;
use Webkul\BagistoApi\Dto\UpdateProductReviewInput;
use Webkul\BagistoApi\Models\ProductReview;

/**
 * Custom ReadProvider for ProductReview mutations
 * Handles reading ProductReview items for update mutations
 */
class ProductReviewReadProvider extends BaseReadProvider
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // Check if this is an update mutation with UpdateProductReviewInput
        $input = $context['input'] ?? null;

        if ($input instanceof UpdateProductReviewInput) {
            $reviewId = $input->id;

            // Extract numeric ID if IRI format
            if (is_string($reviewId) && preg_match('/\/(\d+)$/', $reviewId, $matches)) {
                $reviewId = (int) $matches[1];
            } else {
                $reviewId = (int) $reviewId;
            }

            // Load the existing review
            $review = ProductReview::find($reviewId);

            if ($review) {
                return $review;
            }
        }

        // Fall back to parent implementation
        return parent::provide($operation, $uriVariables, $context);
    }
}
