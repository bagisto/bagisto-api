<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Laravel\Eloquent\State\LinksHandler;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Generic cursor-aware collection provider for GraphQL queries.
 *
 * API Platform's default CollectionProvider uses page-based pagination
 * which ignores cursor arguments (after/before). This provider properly
 * decodes cursor arguments and applies offset-based pagination so that
 * Relay-style cursor pagination works correctly.
 *
 * Can be used by any model that needs working cursor pagination without
 * custom query logic.
 */
class CursorAwareCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly LinksHandler $linksHandler,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $resourceClass = $operation->getClass();
        $model = new $resourceClass;

        $args = $context['args'] ?? [];

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $limit = $first ?? $last ?? 10;
        $offset = 0;

        if ($after) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        if ($before) {
            $decoded = base64_decode($before, true);
            $cursor = ctype_digit((string) $decoded) ? (int) $decoded : 0;
            $offset = max(0, $cursor - $limit);
        }

        $query = $this->linksHandler->handleLinks(
            $model->newQuery(),
            $uriVariables,
            ['operation' => $operation, 'modelClass' => $operation->getClass()] + $context
        );

        $filters = $context['filters'] ?? [];

        foreach ($filters as $column => $value) {
            if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), $column)) {
                $query->where($column, $value);
            }
        }

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $limit);
        }

        $items = $query
            ->offset($offset)
            ->limit($limit)
            ->get();

        $currentPage = $total > 0 ? (int) floor($offset / $limit) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator(
                $items,
                $total,
                $limit,
                $currentPage,
                ['path' => request()->url()]
            )
        );
    }
}
