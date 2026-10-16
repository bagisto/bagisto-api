<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\RateLimiter;
use Webkul\BagistoApi\Dto\ProductReviewTranslationOutput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\MagicAI\Facades\MagicAI;
use Webkul\Product\Repositories\ProductReviewRepository;

class ProductReviewTranslationProvider implements ProviderInterface
{
    /**
     * Translations allowed per minute and IP, as core's translate route allows.
     */
    private const MAX_ATTEMPTS = 10;

    /**
     * Create a new provider instance.
     */
    public function __construct(protected ProductReviewRepository $reviews) {}

    /**
     * Translate a public review after checking its product and approval state.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ProductReviewTranslationOutput
    {
        $args = $context['args'] ?? [];
        $productId = (int) ($uriVariables['productId'] ?? $args['productId'] ?? request()->route('productId'));
        $reviewId = (int) ($uriVariables['reviewId'] ?? $args['reviewId'] ?? request()->route('reviewId'));

        return $this->provideForProductReview($productId, $reviewId);
    }

    /**
     * Translate an approved review of the product into the current locale, throttled per IP as each call is a paid Magic AI request.
     */
    public function provideForProductReview(int $productId, int $reviewId): ProductReviewTranslationOutput
    {
        $key = 'review-translation:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.rate-limit.too-many-attempts', ['seconds' => RateLimiter::availableIn($key)]), 429);
        }

        RateLimiter::hit($key, 60);

        if (
            ! core()->getConfigData('magic_ai.general.settings.enabled')
            || ! core()->getConfigData('magic_ai.storefront_features.review_translation.enabled')
        ) {
            throw new AuthorizationException(trans('shop::app.errors.403.title'));
        }

        $review = $this->reviews->find($reviewId);

        if (
            ! $review
            || (int) $review->product_id !== $productId
        ) {
            throw new ResourceNotFoundException(trans('shop::app.products.view.reviews.empty-review'));
        }

        if ($review->status !== 'approved') {
            throw new InvalidInputException(trans('shop::app.products.view.reviews.empty-review'));
        }

        try {
            $content = MagicAI::translate($review->comment, core()->getCurrentLocale()->name);
        } catch (\Throwable $exception) {
            report($exception);

            throw new InvalidInputException(trans('shop::app.errors.500.title'), 500, $exception);
        }

        $output = new ProductReviewTranslationOutput;
        $output->content = (string) $content;

        return $output;
    }
}
