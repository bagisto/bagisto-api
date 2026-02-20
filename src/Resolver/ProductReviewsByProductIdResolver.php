<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Models\ProductReview;
use Webkul\BagistoApi\Service\GenericIdNormalizer;

/**
 * Query resolver to fetch all product reviews by product ID
 *
 * Supports both numeric product IDs and IRI format for product identification.
 * Returns paginated collection of reviews for a given product.
 *
 * @example Query usage:
 * query {
 *   productReviewsByProductId(productId: 1) {
 *     edges { node { id title rating comment } }
 *     totalCount
 *   }
 * }
 */
class ProductReviewsByProductIdResolver implements QueryCollectionResolverInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    public function __invoke(?iterable $collection, Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $args = $context['args'] ?? [];

        $productId = $args['productId'] ?? null;

        if ($productId === null || $productId === '') {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.product-review.product-id-required')
            );
        }

        $numericProductId = GenericIdNormalizer::extractNumericId($productId);

        if ($numericProductId === null) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.product-review.invalid-product-id-format')
            );
        }

        $query = ProductReview::where('product_id', $numericProductId);

        // Apply status filter
        if (isset($args['status'])) {
            $query->where('status', (int) $args['status']);
        }

        // Apply rating filter
        if (! empty($args['rating'])) {
            $query->where('rating', (int) $args['rating']);
        }

        // Eager load relationships
        $query->with(['product', 'customer']);

        // Apply cursor-based pagination
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        if ($after) {
            $query->where('id', '>', (int) base64_decode($after));
        }
        if ($before) {
            $query->where('id', '<', (int) base64_decode($before));
        }

        $query->orderBy('id', 'asc');
        $perPage = $first ?? $last ?? 30;
        $paginator = $query->paginate($perPage);

        return new Paginator($paginator);
    }
}
