<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\CustomerAddress;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\State\Concerns\ResolvesListSort;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\Customer;

class CustomerAddressProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;
    use ResolvesListSort;

    /**
     * Create a new instance.
     */
    public function __construct(private readonly Pagination $pagination) {}

    /**
     * Provide paginated customer addresses for the authenticated user.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.token-required'));
        }

        $authenticatedCustomerId = $this->getCustomerIdFromToken($token);

        if ($authenticatedCustomerId === null) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.customer-addresses.invalid-or-expired-token'));
        }

        $args = $context['args'] ?? [];

        $requested = $this->requestedCursorLimit($args, 10);
        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

        [$sortColumn, $sortDirection] = $this->resolveListSort($args, ['id', 'created_at'], 'asc');

        $query = CustomerAddress::where('customer_id', $authenticatedCustomerId)
            ->orderBy($sortColumn, $sortDirection);

        if ($sortColumn !== 'id') {
            $query->orderBy('id', $sortDirection);
        }

        return new Paginator($this->paginateCursorWindow($query, $args, $limit));
    }

    /**
     * The id of the non-suspended customer behind a customer access token.
     */
    private function getCustomerIdFromToken(string $token): ?int
    {
        $customer = CurrentCustomer::fromToken($token);

        if (
            ! $customer
            || $customer->is_suspended
        ) {
            return null;
        }

        return $customer->id;
    }
}
