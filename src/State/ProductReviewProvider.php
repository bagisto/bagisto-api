<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
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
        /** Default to approved reviews for storefront API */
        $query->where('status', isset($args['status']) ? (string) $args['status'] : 'approved');
        if (! empty($args['rating'])) {
            $query->where('rating', (int) $args['rating']);
        }

        // Eager load relationships
        $query->with(['product', 'customer']);

        // Cursor-based pagination (offset-based cursors from API Platform)
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $perPage = $first ?? $last ?? 30;
        $offset = 0;

        if ($after) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        if ($before) {
            $decoded = base64_decode($before, true);
            $cursor = ctype_digit((string) $decoded) ? (int) $decoded : 0;
            $offset = max(0, $cursor - $perPage);
        }

        $query->orderBy('id', 'asc');

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $perPage);
        }

        $items = $query
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $currentPage = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url()]
            )
        );
    }
}
