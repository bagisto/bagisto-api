<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Category;

/**
 * Provider for fetching category tree structure with optional parentId filtering
 */
class CategoryTreeProvider implements ProviderInterface
{
    /**
     * Provide category tree data from REST API requests
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): object|array|null {
        $parentId = request('parentId');

        if ($parentId) {
            $parent = Category::find($parentId);

            if (! $parent) {
                return [];
            }

            return $parent->children()
                ->with('children')
                ->where('status', 1)
                ->orderBy('position', 'ASC')
                ->get();
        }

        return Category::query()
            ->with('children')
            ->where('status', 1)
            ->orderBy('position', 'ASC')
            ->whereIsRoot()
            ->get();
    }
}
