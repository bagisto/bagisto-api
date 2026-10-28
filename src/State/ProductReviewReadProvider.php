<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\GraphQl\State\Provider\ReadProvider as BaseReadProvider;
use ApiPlatform\Metadata\Operation;
use Webkul\BagistoApi\Dto\UpdateProductReviewInput;
use Webkul\BagistoApi\Models\ProductReview;

class ProductReviewReadProvider extends BaseReadProvider
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $input = $context['input'] ?? null;

        if ($input instanceof UpdateProductReviewInput) {
            $reviewId = $input->id;

            if (
                is_string($reviewId)
                && preg_match('/\/(\d+)$/', $reviewId, $matches)
            ) {
                $reviewId = (int) $matches[1];
            } else {
                $reviewId = (int) $reviewId;
            }

            $review = ProductReview::find($reviewId);

            if ($review) {
                return $review;
            }
        }

        return parent::provide($operation, $uriVariables, $context);
    }
}
