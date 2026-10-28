<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Models\ProductReview;
use Webkul\BagistoApi\Service\GenericIdNormalizer;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;

class ProductReviewsByProductIdResolver implements QueryCollectionResolverInterface
{
    use ClampsPageSize;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Handle the invoke magic method.
     */
    public function __invoke(?iterable $collection, Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $args = $context['args'] ?? [];

        $productId = $args['productId'] ?? null;

        if (
            $productId === null
            || $productId === ''
        ) {
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

        if (isset($args['status'])) {
            $query->where('status', (int) $args['status']);
        }

        if (! empty($args['rating'])) {
            $query->where('rating', (int) $args['rating']);
        }

        $query->with(['product', 'customer']);

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
        $perPage = $this->clampPageSize($first ?? $last ?? 30);
        $paginator = $query->paginate($perPage);

        return new Paginator($paginator);
    }
}
