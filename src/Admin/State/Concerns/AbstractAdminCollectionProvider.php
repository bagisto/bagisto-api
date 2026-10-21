<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;

abstract class AbstractAdminCollectionProvider implements ProviderInterface
{
    use ResolvesAdminPaging;

    /**
     * The default per page constant.
     */
    protected const DEFAULT_PER_PAGE = 10;

    /**
     * The max per page constant.
     */
    protected const MAX_PER_PAGE = 50;

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
     * @return Builder|\Illuminate\Database\Eloquent\Builder
     */
    abstract protected function buildQuery(array $args);

    /**
     * Apply WHERE / HAVING clauses to restrict the result set.
     *
     * @param  Builder|\Illuminate\Database\Eloquent\Builder  $query
     */
    abstract protected function applyFilters($query, array $args): void;

    /**
     * Apply ORDER BY to the query using resolveSort() internally.
     *
     * @param  Builder|\Illuminate\Database\Eloquent\Builder  $query
     */
    abstract protected function applySort($query, array $args): void;

    /**
     * Map a raw DB stdClass row to the resource DTO.
     */
    abstract protected function mapRow(object $row): object;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        AdminReadPermissions::authorize($operation, null, $this->readPermission());

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
        $items = $this->mapRows($rows);

        return new Paginator(new LengthAwarePaginator(
            $items, $total, $perPage, $page, ['path' => request()->url()]
        ));
    }

    /**
     * Map the page's raw rows to DTOs. Default = one mapRow() per row.
     * Override to batch-load anything that would otherwise be an N+1 across rows.
     *
     * @param  Collection  $rows
     * @return array<int, object>
     */
    protected function mapRows($rows): array
    {
        return $rows->map(fn ($row) => $this->mapRow($row))->all();
    }

    /**
     * Dual-form sort resolver.
     * Handles both compound form (`?sort=name-asc`) and split form.
     *
     * @return array{0: string, 1: string} [column, direction]
     */
    protected function resolveSort(array $args): array
    {
        $sortable = $this->getSortable();
        $default = $sortable[0] ?? 'id';

        $sort = $args['sort'] ?? null;
        $order = $args['order'] ?? null;

        if (
            is_string($sort)
            && str_contains($sort, '-')
        ) {
            [$column, $direction] = explode('-', $sort, 2);
            $sort = $column;
            $order = $order ?? $direction;
        }

        $sort = in_array($sort, $sortable, true) ? $sort : $default;
        $order = strtolower((string) $order) === 'asc' ? 'asc' : 'desc';

        return [$sort, $order];
    }

    /**
     * Count total rows matching the current filters.
     * Uses a subquery wrapping strategy to avoid MySQL errors when the query.
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
     * Override to require a specific ACL key instead of the resource map entry.
     */
    protected function readPermission(): ?string
    {
        return null;
    }
}
