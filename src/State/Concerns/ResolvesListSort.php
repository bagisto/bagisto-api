<?php

namespace Webkul\BagistoApi\State\Concerns;

trait ResolvesListSort
{
    protected function resolveListSort(array $args, array $sortable, string $defaultDirection = 'asc'): array
    {
        $defaultColumn = $sortable[0] ?? 'id';

        $rawSort = $args['sort'] ?? request()->query('sort');
        $rawOrder = $args['order'] ?? request()->query('order');

        $column = $defaultColumn;
        $direction = $defaultDirection;

        if (is_string($rawSort) && $rawSort !== '') {
            if (str_contains($rawSort, '-')) {
                [$col, $dir] = array_pad(explode('-', $rawSort, 2), 2, '');
                $column = $col;
                $direction = $dir !== '' ? $dir : $direction;
            } else {
                $column = $rawSort;
            }
        }

        if (is_string($rawOrder) && $rawOrder !== '') {
            $direction = $rawOrder;
        }

        if (! in_array($column, $sortable, true)) {
            $column = $defaultColumn;
        }

        $direction = strtolower((string) $direction) === 'desc' ? 'desc' : 'asc';

        return [$column, $direction];
    }
}
