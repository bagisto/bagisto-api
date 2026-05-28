<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Centralised scaffolding for admin collection providers.
 *
 * Handles: auth check, args extraction (REST query + GraphQL $context['args']),
 * resolvePaging (with cursor `after` decoding), resolveSort (dual-form compound
 * and split), subquery count for GROUP BY queries, and Paginator wrap.
 *
 * Concrete providers implement only:
 *   - getSortable()       → string[] allow-list (first entry = default sort column)
 *   - buildQuery($args)   → the base DB query (with joins, select, where, groupBy)
 *   - applyFilters($query, $args) → additional WHERE clauses
 *   - applySort($query, $args)    → ORDER BY (using resolveSort() internally)
 *   - mapRow($row)        → convert a stdClass DB row to a DTO
 *
 * Override resolvePaging() or countTotal() when the resource needs bespoke logic.
 */
abstract class AbstractAdminCollectionProvider implements ProviderInterface
{
    protected const DEFAULT_PER_PAGE = 10;

    protected const MAX_PER_PAGE = 50;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $args = $context['args'] ?? array_merge(
            request()->query(),
            request()->input('filter') ?? []
        );

        [$page, $perPage] = $this->resolvePaging($args);

        $query = $this->buildQuery($args);
        $this->applyFilters($query, $args);
        $this->applySort($query, $args);

        $total = $this->countTotal($query);

        $rows = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();
        $items = $rows->map(fn ($row) => $this->mapRow($row))->all();

        return new Paginator(new LengthAwarePaginator(
            $items, $total, $perPage, $page, ['path' => request()->url()]
        ));
    }

    /**
     * Resolve [page, perPage] from REST query params or GraphQL args (first/after).
     *
     * Cursor `after` is the API Platform convention: base64-encoded 0-based offset
     * of the last returned item (e.g. after returning items 0-9, cursor = base64("9")).
     */
    protected function resolvePaging(array $args): array
    {
        $first = $args['first'] ?? null;
        $perPage = $first !== null
            ? (int) $first
            : (int) ($args['per_page'] ?? static::DEFAULT_PER_PAGE);

        if ($perPage <= 0) {
            $perPage = static::DEFAULT_PER_PAGE;
        }

        $perPage = min($perPage, static::MAX_PER_PAGE);

        // GraphQL cursor: base64-encoded 0-based index of the last returned item.
        if (! empty($args['after'])) {
            $decoded = base64_decode((string) $args['after'], true);
            if ($decoded !== false && ctype_digit($decoded)) {
                $offset = (int) $decoded + 1;
                $page = (int) floor($offset / $perPage) + 1;

                return [$page, $perPage];
            }
        }

        $page = max(1, (int) ($args['page'] ?? 1));

        return [$page, $perPage];
    }

    /**
     * Dual-form sort resolver.
     *
     * Handles both compound form (`?sort=name-asc`) and split form
     * (`?sort=name&order=desc`). Unknown column names fall back to the first
     * entry in getSortable(). Direction defaults to 'desc'.
     *
     * @return array{0: string, 1: string} [column, direction]
     */
    protected function resolveSort(array $args): array
    {
        $sortable = $this->getSortable();
        $default = $sortable[0] ?? 'id';

        $sort = $args['sort'] ?? null;
        $order = $args['order'] ?? null;

        if (is_string($sort) && str_contains($sort, '-')) {
            [$col, $dir] = explode('-', $sort, 2);
            $sort = $col;
            $order = $order ?? $dir;
        }

        $sort = in_array($sort, $sortable, true) ? $sort : $default;
        $order = strtolower((string) $order) === 'asc' ? 'asc' : 'desc';

        return [$sort, $order];
    }

    /**
     * Count total rows matching the current filters.
     *
     * Uses a subquery wrapping strategy to avoid MySQL errors when the query
     * uses GROUP BY or aggregate columns — the same pattern used across all
     * admin datagrid queries. Override when simpler counting suffices.
     */
    protected function countTotal($query): int
    {
        $countQuery = clone $query;
        $countSql = $countQuery->reorder()->toSql();
        $countBindings = $countQuery->getBindings();

        return DB::table(DB::raw('('.$countSql.') as sub'))
            ->setBindings($countBindings)
            ->count();
    }

    /**
     * Columns valid as sort targets. First entry is the default sort column.
     *
     * @return string[]
     */
    abstract protected function getSortable(): array;

    /**
     * Return the base query builder (with joins, select, groupBy) before filters and sort.
     *
     * @param  array  $args  Merged REST query params / GraphQL args
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    abstract protected function buildQuery(array $args);

    /**
     * Apply WHERE / HAVING clauses to restrict the result set.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     */
    abstract protected function applyFilters($query, array $args): void;

    /**
     * Apply ORDER BY to the query using resolveSort() internally.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     */
    abstract protected function applySort($query, array $args): void;

    /**
     * Map a raw DB stdClass row to the resource DTO.
     */
    abstract protected function mapRow(object $row): object;
}
