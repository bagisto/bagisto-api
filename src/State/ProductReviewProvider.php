<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\ProductReview;

/**
 * Storefront product reviews (REST + GraphQL), mirroring the shop ReviewController: only approved
 * reviews are listed, and reviewer names are censored when the store setting is on.
 */
class ProductReviewProvider implements ProviderInterface
{
    private const DEFAULT_PER_PAGE = 30;

    private const MAX_PER_PAGE = 50;

    private const STATUS_APPROVED = 'approved';

    public function __construct(
        private readonly Pagination $pagination
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof Get) {
            return $this->findVisible((int) ($uriVariables['id'] ?? 0));
        }

        $isGraphQL = ! empty($context['graphql_operation_name']);

        $args = $this->resolveArgs($uriVariables, $context, $isGraphQL);

        $query = ProductReview::query();

        if (! empty($args['product_id'])) {
            $query->where('product_id', (int) $args['product_id']);
        }

        $query->where('status', self::STATUS_APPROVED);

        if (! empty($args['rating'])) {
            $query->where('rating', (int) $args['rating']);
        }

        $query->with(['product', 'customer'])->orderBy('id', 'asc');

        return $isGraphQL
            ? $this->graphQlPaginate($query, $args)
            : $this->restPaginate($query, $args);
    }

    /**
     * GraphQL args arrive in $context['args']. REST filters arrive as query
     * params, and the nested route supplies the parent product via uriVariables.
     */
    private function resolveArgs(array $uriVariables, array $context, bool $isGraphQL): array
    {
        if ($isGraphQL) {
            return $context['args'] ?? [];
        }

        $args = request()->query();

        $productId = $uriVariables['productId'] ?? $uriVariables['id'] ?? ($args['product_id'] ?? null);

        if ($productId !== null) {
            $args['product_id'] = $productId;
        }

        return $args;
    }

    /**
     * REST offset pagination — honours ?page, ?per_page (and ?limit alias),
     * default 30, capped at 50. Mirrors the storefront pagination convention.
     */
    private function restPaginate($query, array $args): Paginator
    {
        $perPage = (int) ($args['per_page'] ?? $args['limit'] ?? self::DEFAULT_PER_PAGE);

        if ($perPage < 1) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $perPage = min($perPage, self::MAX_PER_PAGE);

        $page = max(1, (int) ($args['page'] ?? 1));
        $total = (clone $query)->count();

        $items = $this->censor($query->offset(($page - 1) * $perPage)->limit($perPage)->get());

        return new Paginator(
            new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url()]
            )
        );
    }

    /**
     * GraphQL cursor pagination (offset-based cursors from API Platform).
     * Behaviour unchanged from the original provider.
     */
    private function graphQlPaginate($query, array $args): Paginator
    {
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $perPage = $first ?? $last ?? self::DEFAULT_PER_PAGE;
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

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $perPage);
        }

        $items = $this->censor($query->offset($offset)->limit($perPage)->get());

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

    /**
     * One review: an approved one, or the signed-in customer's own review in any status.
     */
    public function findVisible(int $id): ProductReview
    {
        $customerId = Auth::guard('sanctum')->user()?->id;

        $review = ProductReview::query()
            ->whereKey($id)
            ->where(fn ($query) => $query->where('status', self::STATUS_APPROVED)
                ->when($customerId, fn ($query) => $query->orWhere('customer_id', $customerId)))
            ->first();

        if (! $review) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.product-review.not-found', ['id' => $id]));
        }

        return $this->censor(collect([$review]))->first();
    }

    /**
     * Censor reviewer names as the storefront does, without saving the change.
     */
    private function censor(Collection $reviews): Collection
    {
        if (! core()->getConfigData('catalog.products.review.censoring_reviewer_name')) {
            return $reviews;
        }

        return $reviews->each(fn ($review) => $review->name = collect(explode(' ', (string) $review->name))
            ->map(fn ($part) => mb_substr($part, 0, 1).str_repeat('*', max(mb_strlen($part) - 1, 0)))
            ->join(' '));
    }
}
