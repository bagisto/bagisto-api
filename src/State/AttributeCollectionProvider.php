<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Attribute;

class AttributeCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $args = $context['args'] ?? [];

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $defaultPerPage = 30;

        // Determine page size
        if ($first !== null) {
            $perPage = $first;
        } elseif ($last !== null) {
            $perPage = $last;
        } else {
            $perPage = $defaultPerPage;
        }

        $query = Attribute::query();

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

            // Load relations with translations
            foreach ($items as $item) {
                $item->load(['options', 'translations', 'options.translations']);
            }

            // Update items in paginator
            $laravelPaginator->setCollection(collect($items));

            return new Paginator($laravelPaginator);
        }

        // Load relations with translations
        $query->with(['options', 'translations', 'options.translations']);

        // Order by ID for consistent pagination (ascending for after/default)
        $query->orderBy('id', 'asc');

        // Paginate with the specified per page amount
        $laravelPaginator = $query->paginate($perPage);

        // Return API Platform paginator which handles totalCount correctly
        return new Paginator($laravelPaginator);
    }
}
