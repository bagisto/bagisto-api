<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Category;

/**
 * Provider for fetching category tree structure for REST API
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
            $parent = Category::find($parentId);

            if (! $parent) {
                return [];
            }

            $children = $parent->children()
                ->where('status', 1)
                ->orderBy('position', 'ASC')
                ->get();

            return $this->attachChildrenRecursive($children, 0, $depth);
        }

        $categories = Category::query()
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
