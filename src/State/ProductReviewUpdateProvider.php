<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\ProductReview;

/**
 * Provider for ProductReview update mutations
 * Loads the ProductReview from the input's ID
 */
class ProductReviewUpdateProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // The input data will contain the ID
        // In GraphQL, this is available in the context
        $normalizationFormat = $context['is_mutation'] ?? false;

        // Try to get the input from various possible locations
        $input = null;

        // Check if we have the raw denormalized input in context
        if (isset($context['input'])) {
            $input = $context['input'];
        }
        // Check in request body
        elseif (isset($context['request']) && $context['request']->getMethod() === 'POST') {
            $input = $context['request']->getContent();
        }

        // Extract ID from input
        $reviewId = null;

        if (is_object($input) && isset($input->id)) {
            $reviewId = $input->id;
        } elseif (is_array($input) && isset($input['id'])) {
            $reviewId = $input['id'];
        } elseif (isset($uriVariables['id'])) {
            $reviewId = $uriVariables['id'];
        }

        if (! $reviewId) {
            return null;
        }

        // Convert IRI format to numeric ID if needed
        if (is_string($reviewId)) {
            if (preg_match('/\/(\d+)$/', $reviewId, $matches)) {
                $reviewId = (int) $matches[1];
            } else {
                $reviewId = (int) $reviewId;
            }
        }

        // Load the existing review
        $review = ProductReview::find($reviewId);

        return $review;
    }
}
