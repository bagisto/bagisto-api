<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Models\AdminCategoryTree;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\Category\Models\Category;

class AdminCategoryTreeProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        AdminReadPermissions::authorize($operation, AdminCategoryTree::class);

        $args = $context['args'] ?? request()->query();
        $locale = $args['locale'] ?? app()->getLocale();
        $rootId = isset($args['rootId']) ? (int) $args['rootId'] : null;

        $statusFilter = (isset($args['status']) && in_array((string) $args['status'], ['0', '1'], true))
            ? (int) $args['status']
            : null;

        if ($rootId !== null) {
            if (! Category::where('id', $rootId)->exists()) {
                return new Paginator(new LengthAwarePaginator([], 0, 50, 1, ['path' => request()->url()]));
            }

            $nodes = Category::query()
                ->whereDescendantOrSelf($rootId)
                ->with(['translations' => fn ($query) => $query->where('locale', $locale)])
                ->defaultOrder()
                ->get()
                ->toTree();
        } else {
            $nodes = Category::with(['translations' => fn ($query) => $query->where('locale', $locale)])
                ->defaultOrder()
                ->get()
                ->toTree();
        }

        $items = $nodes
            ->map(fn ($node) => $this->mapNode($node, $locale, $statusFilter))
            ->filter()
            ->values()
            ->all();

        $count = count($items);

        $perPage = max(50, $count);

        return new Paginator(new LengthAwarePaginator(
            $items, $count, $perPage, 1, ['path' => request()->url()]
        ));
    }

    /**
     * Recursively map a Nestedset node to the AdminCategoryTree DTO shape.
     * The top-level return is an AdminCategoryTree DTO so API Platform can.
     */
    protected function mapNode(mixed $node, string $locale, ?int $statusFilter): ?AdminCategoryTree
    {
        $mappedChildren = $node->children
            ->map(fn ($child) => $this->mapNodeAsArray($child, $locale, $statusFilter))
            ->filter()
            ->values()
            ->all();

        $matchesStatus = $statusFilter === null || (int) $node->status === $statusFilter;

        if (
            ! $matchesStatus
            && empty($mappedChildren)
        ) {
            return null;
        }

        $translation = $node->translations->first();

        $dto = new AdminCategoryTree;
        $dto->id = (int) $node->id;
        $dto->name = $translation?->name;
        $dto->slug = $translation?->slug;
        $dto->status = (int) $node->status;
        $dto->position = (int) ($node->position ?? 0);
        $dto->parentId = $node->parent_id !== null ? (int) $node->parent_id : null;
        $dto->displayMode = $node->display_mode ?? null;
        $dto->children = $mappedChildren;

        return $dto;
    }

    /**
     * Recursively map a node (and its descendants) to a plain associative array.
     * Used for all non-root nodes so they are never treated as ApiResource objects.
     *
     * @return array<string, mixed>|null
     */
    protected function mapNodeAsArray(mixed $node, string $locale, ?int $statusFilter): ?array
    {
        $mappedChildren = $node->children
            ->map(fn ($child) => $this->mapNodeAsArray($child, $locale, $statusFilter))
            ->filter()
            ->values()
            ->all();

        $matchesStatus = $statusFilter === null || (int) $node->status === $statusFilter;

        if (
            ! $matchesStatus
            && empty($mappedChildren)
        ) {
            return null;
        }

        $translation = $node->translations->first();

        return [
            'id' => (int) $node->id,
            'name' => $translation?->name,
            'slug' => $translation?->slug,
            'status' => (int) $node->status,
            'position' => (int) ($node->position ?? 0),
            'parentId' => $node->parent_id !== null ? (int) $node->parent_id : null,
            'displayMode' => $node->display_mode ?? null,
            'children' => $mappedChildren,
        ];
    }
}
