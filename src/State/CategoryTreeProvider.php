<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Category;
use Webkul\Category\Models\Category as BaseCategory;

/**
 * Provider for fetching category tree structure for REST API
 *
 * This provider handles the /api/shop/category-trees endpoint and supports:
 * - parentId query parameter to filter by parent category
 * - depth query parameter to control how deep the tree goes (default: 4)
 * - Returns hierarchical category structure with translations
 */
class CategoryTreeProvider implements ProviderInterface
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
            $parent = BaseCategory::with(['translations', 'translation'])
                ->find($parentId);

            if (! $parent) {
                return [];
            }

            $children = $parent->children()
                ->with(['translations', 'translation'])
                ->where('status', 1)
                ->orderBy('position', 'ASC')
                ->get();

            $tree = $this->buildTreeArray($children, 0, $depth);

            return $tree;
        }

        // Get root categories
        $categories = BaseCategory::query()
            ->with(['translations', 'translation'])
            ->where('status', 1)
            ->orderBy('position', 'ASC')
            ->whereIsRoot()
            ->get();

        return $this->buildTreeArray($categories, 0, $depth);
    }

    /**
     * Build tree as array to avoid serialization issues
     */
    private function buildTreeArray($categories, int $currentDepth = 0, int $maxDepth = self::MAX_DEPTH): array
    {
        $result = [];

        if ($currentDepth >= $maxDepth) {
            return $result;
        }

        foreach ($categories as $category) {
            $item = [
                'id'          => $category->id,
                'position'    => $category->position,
                'status'      => $category->status,
                'displayMode' => $category->display_mode,
                '_lft'        => $category->_lft,
                '_rgt'        => $category->_rgt,
                'createdAt'   => $category->created_at?->toIso8601String(),
                'updatedAt'   => $category->updated_at?->toIso8601String(),
                'url'         => $category->url,
            ];

            // Add translation if available
            $translation = $category->translation;
            if ($translation) {
                $item['translation'] = [
                    'id'              => $translation->id,
                    'categoryId'      => $translation->category_id,
                    'name'            => $translation->name,
                    'slug'            => $translation->slug,
                    'urlPath'         => $translation->url_path,
                    'description'     => $translation->description,
                    'metaTitle'       => $translation->meta_title,
                    'metaDescription' => $translation->meta_description,
                    'metaKeywords'    => $translation->meta_keywords,
                    'locale'          => $translation->locale,
                ];
            }

            // Add children if not at max depth
            if ($currentDepth < $maxDepth - 1) {
                $children = $category->children()
                    ->with(['translations', 'translation'])
                    ->where('status', 1)
                    ->orderBy('position', 'ASC')
                    ->get();

                if ($children->count() > 0) {
                    $item['children'] = $this->buildTreeArray($children, $currentDepth + 1, $maxDepth);
                } else {
                    $item['children'] = [];
                }
            } else {
                $item['children'] = [];
            }

            $result[] = $item;
        }

        return $result;
    }
}
