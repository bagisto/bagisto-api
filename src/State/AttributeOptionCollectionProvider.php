<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Models\AttributeOption;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;

class AttributeOptionCollectionProvider implements ProviderInterface
{
    use ResolvesCursorWindow;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination,
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $attributeId = $uriVariables['attribute_id'] ?? $uriVariables['attributeId'] ?? null;

        $args = $context['args'] ?? [];

        if (
            ! $attributeId
            && isset($args['attributeId'])
        ) {
            $attributeId = (int) $args['attributeId'];
        }

        if (! $attributeId) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.attribute.option-id-required')
            );
        }

        $limit = $this->requestedCursorLimit($args, 10);

        $query = AttributeOption::where('attribute_id', $attributeId)
            ->with('translations')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');

        return new Paginator($this->paginateCursorWindow($query, $args, $limit));
    }
}
