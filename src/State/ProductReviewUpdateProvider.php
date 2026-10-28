<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\ProductReview;

class ProductReviewUpdateProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $normalizationFormat = $context['is_mutation'] ?? false;

        $input = null;

        if (isset($context['input'])) {
            $input = $context['input'];
        } elseif (
            isset($context['request'])
            && $context['request']->getMethod() === 'POST'
        ) {
            $input = $context['request']->getContent();
        }

        $reviewId = null;

        if (
            is_object($input)
            && isset($input->id)
        ) {
            $reviewId = $input->id;
        } elseif (
            is_array($input)
            && isset($input['id'])
        ) {
            $reviewId = $input['id'];
        } elseif (isset($uriVariables['id'])) {
            $reviewId = $uriVariables['id'];
        }

        if (! $reviewId) {
            return null;
        }

        if (is_string($reviewId)) {
            if (preg_match('/\/(\d+)$/', $reviewId, $matches)) {
                $reviewId = (int) $matches[1];
            } else {
                $reviewId = (int) $reviewId;
            }
        }

        $review = ProductReview::find($reviewId);

        return $review;
    }
}
