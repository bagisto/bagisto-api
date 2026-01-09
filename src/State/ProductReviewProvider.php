<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\ProductReview;

/**
 * Provider for ProductReview queries
 * Handles filtering by product_id, status, and rating
 */
class ProductReviewProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $args = $context['args'] ?? [];
        $query = ProductReview::query();

        // Apply filters
        if (! empty($args['product_id'])) {
            $query->where('product_id', (int) $args['product_id']);
        }
        if (isset($args['status'])) {
            $query->where('status', (int) $args['status']);
        }
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
