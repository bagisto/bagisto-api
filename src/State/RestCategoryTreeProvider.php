<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Category;

/**
 * Provider for fetching category tree structure for REST API
 *
 * This provider handles the /api/shop/categories/tree endpoint and supports:
 * - parentId query parameter to filter by parent category
 * - depth query parameter to control how deep the tree goes (default: 4)
 * - Returns hierarchical category structure with translations
 */
class RestCategoryTreeProvider implements ProviderInterface
{
    private const MAX_DEPTH = 4;

    /**
     * Provide category tree data from REST API requests
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): object|array|null {
        $parentId = $context['filters']['parentId'] ?? request('parentId');
        $depth = (int) (request('depth') ?? self::MAX_DEPTH);

        if ($parentId) {
            $parent = Category::with(['translations', 'translation'])
                ->find($parentId);

            if (! $parent) {
                return [];
            }

            $children = $parent->children()
                ->with(['translations', 'translation'])
                ->where('status', 1)
                ->orderBy('position', 'ASC')
                ->get();

            return $this->attachChildrenRecursive($children, 0, $depth);
        }

        $categories = Category::query()
            ->with(['translations', 'translation'])
            ->where('status', 1)
            ->orderBy('position', 'ASC')
            ->whereIsRoot()
            ->get();

        return $this->attachChildrenRecursive($categories, 0, $depth);
    }

    /**
     * Attach children recursively to each category up to max depth
     */
    private function attachChildrenRecursive($categories, int $currentDepth = 0, int $maxDepth = self::MAX_DEPTH)
    {
        if ($currentDepth >= $maxDepth) {
            return $categories;
        }

        foreach ($categories as $category) {
            $children = $category->children()
                ->with(['translations', 'translation'])
                ->where('status', 1)
                ->orderBy('position', 'ASC')
                ->get();

            if ($currentDepth < $maxDepth - 1 && $children->count() > 0) {
                // Attach nested children recursively
                $category->setRelation('children', $this->attachChildrenRecursive($children, $currentDepth + 1, $maxDepth));
            } else {
                $category->setRelation('children', collect([]));
            }
        }

        return $categories;
    }
}
