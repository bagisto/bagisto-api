<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Laravel\Eloquent\State\LinksHandler;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Contracts\PreloadsCollection;
use Webkul\BagistoApi\Models\Category;
use Webkul\BagistoApi\Models\Page;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\State\Concerns\ScopesNestedConnections;

class CursorAwareCollectionProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;
    use ScopesNestedConnections;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly LinksHandler $linksHandler,
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $resourceClass = $operation->getClass();
        $model = new $resourceClass;

        $args = $context['args'] ?? [];

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $requested = $first ?? $last ?? 10;
        $limit = $requested > 0 ? $this->clampPageSize($requested, $operation->getPaginationMaximumItemsPerPage()) : 0;

        $query = $this->linksHandler->handleLinks(
            $model->newQuery(),
            $uriVariables,
            [
                'operation' => $operation,
                'modelClass' => $operation->getClass(),
            ] + $context
        );

        $this->scopeToParent($query, $context);

        if ($model instanceof Category) {
            $query->where($model->getTable().'.status', 1);
        }

        if ($model instanceof Page) {
            $query->inCurrentChannel();
        }

        $filters = $context['filters'] ?? [];

        foreach ($filters as $column => $value) {
            if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), $column)) {
                $query->where($column, $value);
            }
        }

        if (empty($query->getQuery()->orders)) {
            $query->orderBy($model->getQualifiedKeyName());
        }

        $total = (clone $query)->count();

        [$offset, $limit] = $this->resolveCursorWindow($args, $total, $limit);

        $items = $limit > 0
            ? $query->offset($offset)->limit($limit)->get()
            : $model->newCollection();

        if (is_a($model, PreloadsCollection::class)) {
            $model::preloadCollection($items);
        }

        $divisor = max(1, $limit);
        $currentPage = $total > 0 ? (int) floor($offset / $divisor) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator(
                $items,
                $total,
                $divisor,
                $currentPage,
                ['path' => request()->url()]
            )
        );
    }
}
