<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\GdprRequest;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\GdprFeatureGate;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\State\Concerns\ResolvesListSort;
use Webkul\BagistoApi\Support\CurrentCustomer;

class GdprRequestProvider implements ProviderInterface
{
    use ClampsPageSize;
    use GdprFeatureGate;
    use ResolvesCursorWindow;
    use ResolvesListSort;

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
        $this->assertGdprEnabled();

        $customer = CurrentCustomer::user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.gdpr.unauthenticated'));
        }

        $args = $context['args'] ?? [];
        $requested = $this->requestedCursorLimit($args, 30);
        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

        [$sortColumn, $sortDirection] = $this->resolveListSort($args, ['id', 'created_at'], 'desc');

        $query = GdprRequest::where('customer_id', $customer->id)
            ->orderBy($sortColumn, $sortDirection);

        if ($sortColumn !== 'id') {
            $query->orderBy('id', $sortDirection);
        }

        return new Paginator($this->paginateCursorWindow($query, $args, $limit));
    }
}
