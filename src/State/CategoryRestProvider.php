<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Category;

/**
 * REST provider for /categories collection.
 *
 * Storefront scope — always filters to active categories (status=1).
 * Admin-disabled categories are not shoppable and never appear here.
 *
 * Optional query params:
 *   ?parent_id=2 / ?parentId=2  → only direct children of category 2
 *   ?per_page=N & ?page=N       → standard pagination (defaults from config)
 */
class CategoryRestProvider implements ProviderInterface
{
    public function __construct(private readonly Pagination $pagination) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $query = Category::query()->where('status', 1);

        $request = request();
        $parentId = $request->query('parent_id') ?? $request->query('parentId');
        if ($parentId !== null && $parentId !== '') {
            $query->where('parent_id', (int) $parentId);
        }

        $query->orderBy('position', 'asc')->orderBy('id', 'asc');

        $context['filters'] = array_merge($context['filters'] ?? [], $request->query());
        $perPage = $this->pagination->getLimit($operation, $context);
        $page = $this->pagination->getPage($context);

        return new Paginator($query->paginate($perPage, ['*'], 'page', $page));
    }
}
