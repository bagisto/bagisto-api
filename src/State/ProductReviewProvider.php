<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Models\ProductReview;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\State\Concerns\ScopesNestedConnections;

class ProductReviewProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;
    use ScopesNestedConnections;

    /**
     * The default per page constant.
     */
    private const DEFAULT_PER_PAGE = 30;

    /**
     * The max per page constant.
     */
    private const MAX_PER_PAGE = 50;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $isGraphQL = ! empty($context['graphql_operation_name']);

        $args = $this->resolveArgs($uriVariables, $context, $isGraphQL);

        $query = ProductReview::query();

        if ($isGraphQL) {
            $this->scopeToParent($query, $context);
        }

        if (! empty($args['product_id'])) {
            $query->where('product_id', (int) $args['product_id']);
        }

        $query->where('status', ProductReview::STATUS_APPROVED);

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

        $items = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

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
     * GraphQL cursor pagination over offset-based cursors.
     */
    private function graphQlPaginate($query, array $args): Paginator
    {
        $requested = $this->requestedCursorLimit($args, self::DEFAULT_PER_PAGE);

        return new Paginator($this->paginateCursorWindow($query, $args, $requested > 0 ? $this->clampPageSize($requested) : 0));
    }
}
