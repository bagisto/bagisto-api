<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

trait ResolvesCursorWindow
{
    /**
     * Resolve the offset and row count a Relay cursor request selects from a set of the given size.
     *
     * @return array{0: int, 1: int}
     */
    protected function resolveCursorWindow(array $args, int $total, int $limit): array
    {
        $before = $this->decodeCursor($args['before'] ?? null);

        if ($before !== null) {
            $offset = max(0, $before - $limit);

            return [$offset, max(0, min($limit, $before - $offset))];
        }

        if (isset($args['last'])) {
            return [max(0, $total - (int) $args['last']), $limit];
        }

        $after = $this->decodeCursor($args['after'] ?? null);

        return [$after === null ? 0 : $after + 1, $limit];
    }

    /**
     * Resolve the page size a Relay request asks for, where zero or less selects an empty page.
     */
    protected function requestedCursorLimit(array $args, int $default): int
    {
        return max(0, (int) ($args['first'] ?? $args['last'] ?? $default));
    }

    /**
     * Fetch the rows a Relay cursor request selects, ordered by primary key when nothing else orders them.
     */
    protected function paginateCursorWindow(Builder|Relation $query, array $args, int $limit): LengthAwarePaginator
    {
        $model = $query->getModel();

        $baseQuery = $query instanceof Relation ? $query->getBaseQuery() : $query->getQuery();

        if (empty($baseQuery->orders)) {
            $query->orderBy($model->getQualifiedKeyName());
        }

        $total = (clone $query)->count();

        [$offset, $limit] = $this->resolveCursorWindow($args, $total, $limit);

        $items = $limit > 0
            ? $query->offset($offset)->limit($limit)->get()
            : $model->newCollection();

        $perPage = max(1, $limit);

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $total > 0 ? (int) floor($offset / $perPage) + 1 : 1,
            ['path' => request()->url()]
        );
    }

    /**
     * Decode a base64 offset cursor, or return null when it is absent or malformed.
     */
    protected function decodeCursor(?string $cursor): ?int
    {
        if (
            $cursor === null
            || $cursor === ''
        ) {
            return null;
        }

        $decoded = base64_decode($cursor, true);

        return ctype_digit((string) $decoded) ? (int) $decoded : null;
    }
}
