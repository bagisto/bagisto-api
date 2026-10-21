<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CustomerReview;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;

class CustomerReviewProvider implements ProviderInterface
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
     * Provide customer reviews for collection or single-item operations.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (
            ! $operation instanceof GetCollection
            && ! ($operation instanceof QueryCollection)
        ) {
            return $this->provideItem($customer, $uriVariables);
        }

        return $this->provideCollection($customer, $context);
    }

    /**
     * Return a single review owned by the customer.
     */
    private function provideItem(object $customer, array $uriVariables): CustomerReview
    {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-review.id-required'));
        }

        $review = CustomerReview::where('customer_id', $customer->id)
            ->with(['product', 'customer'])
            ->find($id);

        if (! $review) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.customer-review.not-found', ['id' => $id])
            );
        }

        return $review;
    }

    /**
     * Return a paginated collection of reviews owned by the customer.
     */
    private function provideCollection(object $customer, array $context): Paginator
    {
        $args = $context['args'] ?? [];
        $filters = $context['filters'] ?? [];

        $query = CustomerReview::where('customer_id', $customer->id)
            ->with(['product', 'customer']);

        $status = $args['status'] ?? $filters['status'] ?? null;
        if ($status !== null) {
            $query->where('status', (string) $status);
        }

        $rating = $args['rating'] ?? $filters['rating'] ?? null;
        if ($rating !== null) {
            $query->where('rating', (int) $rating);
        }

        $requested = $this->requestedCursorLimit($args, 10);
        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

        $query->orderBy('id', 'desc');

        return new Paginator($this->paginateCursorWindow($query, $args, $limit));
    }
}
