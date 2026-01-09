<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Models\AttributeOption;

/**
 * Collection provider for AttributeOption
 *
 * Provides cursor-based pagination for attribute options
 * - Subresource: /attributes/{attribute_id}/options (attribute_id provided via URI)
 * - Direct query: attributeOptions(attributeId: 23) (attributeId required in args for GraphQL and REST)
 */
class AttributeOptionCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Handle both 'attributeId' (camelCase) and 'attribute_id' (snake_case) from URI
        $attributeId = $uriVariables['attribute_id'] ?? $uriVariables['attributeId'] ?? null;

        $args = $context['args'] ?? [];

        // Also check for attributeId in GraphQL args (for direct attributeOptions query)
        if (! $attributeId && isset($args['attributeId'])) {
            $attributeId = (int) $args['attributeId'];
        }

        // Enforce: attributeId is required when querying attribute options directly
        if (! $attributeId) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.attribute.option-id-required')
            );
        }

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $defaultPerPage = 10;

        // Determine page size
        if ($first !== null) {
            $perPage = $first;
        } elseif ($last !== null) {
            $perPage = $last;
        } else {
            $perPage = $defaultPerPage;
        }

        $query = AttributeOption::where('attribute_id', $attributeId)
            ->orderBy('sort_order', 'asc');

        // Handle cursor-based pagination
        if ($after) {
            $afterId = (int) base64_decode($after);
            $query->where('id', '>', $afterId);
        } elseif ($before) {
            $beforeId = (int) base64_decode($before);
            $query->where('id', '<', $beforeId);
            // For 'before', we need to reverse order, paginate, then reverse results
            $query->orderBy('id', 'desc');
            $laravelPaginator = $query->paginate($perPage);

            // Reverse the items to maintain proper cursor order
            $items = $laravelPaginator->items();
            $items = array_reverse($items);

            // Load relations
            foreach ($items as $item) {
                $item->load('translations');
            }

            // Update items in paginator
            $laravelPaginator->setCollection(collect($items));

            return $laravelPaginator;
        }

        // Default order
        $query->orderBy('sort_order', 'asc');
        $laravelPaginator = $query->paginate($perPage);

        // Load relations
        foreach ($laravelPaginator as $item) {
            $item->load('translations');
        }

        return $laravelPaginator;
    }
}
