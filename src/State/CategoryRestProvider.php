<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Category;

class CategoryRestProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(private readonly Pagination $pagination) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $query = Category::query()
            ->where('status', 1)
            ->with([
                'translations',
                'filterable_attributes.translations',
                'filterable_attributes.options.translations',
            ]);

        $request = request();
        $parentId = $request->query('parent_id') ?? $request->query('parentId');
        if (
            $parentId !== null
            && $parentId !== ''
        ) {
            $query->where('parent_id', (int) $parentId);
        }

        $query->orderBy('position', 'asc')->orderBy('id', 'asc');

        $context['filters'] = array_merge($context['filters'] ?? [], $request->query());
        $perPage = $this->pagination->getLimit($operation, $context);
        $page = $this->pagination->getPage($context);

        return new Paginator($query->paginate($perPage, ['*'], 'page', $page));
    }
}
