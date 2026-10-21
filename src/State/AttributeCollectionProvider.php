<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Attribute;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;

class AttributeCollectionProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $args = $context['args'] ?? [];

        $requested = $this->requestedCursorLimit($args, 30);
        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

        $query = Attribute::query()
            ->with(['options', 'translations', 'translation', 'options.translations', 'options.translation'])
            ->orderBy('id', 'asc');

        return new Paginator($this->paginateCursorWindow($query, $args, $limit));
    }
}
