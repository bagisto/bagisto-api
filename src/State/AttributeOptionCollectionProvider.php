<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
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

        $offset = 0;

        if ($after) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        if ($before) {
            $decoded = base64_decode($before, true);
            $cursor = ctype_digit((string) $decoded) ? (int) $decoded : 0;
            $offset = max(0, $cursor - $perPage);
        }

        $query = AttributeOption::where('attribute_id', $attributeId)
            ->with('translations')
            ->orderBy('sort_order', 'asc');

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $perPage);
        }

        $items = $query
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $currentPage = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url()]
            )
        );
    }
}
