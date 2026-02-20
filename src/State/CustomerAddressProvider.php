<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Laravel\Eloquent\PartialPaginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\CustomerAddress;
use Webkul\Customer\Models\Customer;

/**
 * Provides customer addresses filtered by authenticated user token.
 */
class CustomerAddressProvider implements ProviderInterface
{
    public function __construct(private readonly Pagination $pagination) {}

    /**
     * Provide paginated customer addresses for the authenticated user.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        // Extract Bearer token from Authorization header
        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.token-required'));
        }

        $authenticatedCustomerId = $this->getCustomerIdFromToken($token);

        if ($authenticatedCustomerId === null) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.customer-addresses.invalid-or-expired-token'));
        }

        $query = CustomerAddress::where('customer_id', $authenticatedCustomerId);

        $isPartial = $operation->getPaginationPartial();
        $collection = $query
            ->{$isPartial ? 'simplePaginate' : 'paginate'}(
                perPage: $this->pagination->getLimit($operation, $context),
                page: $this->pagination->getPage($context),
            );

        if ($isPartial) {
            return new PartialPaginator($collection);
        }

        return new Paginator($collection);
    }

    /**
     * Validate token and extract customer ID.
     */
    private function getCustomerIdFromToken(string $token): ?int
    {
        try {
            $tokenParts = explode('|', $token);

            if (count($tokenParts) !== 2) {
                return null;
            }

            $tokenId = $tokenParts[0];

            $personalAccessToken = DB::table('personal_access_tokens')
                ->where('id', $tokenId)
                ->where('tokenable_type', Customer::class)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if (! $personalAccessToken) {
                return null;
            }

            $customer = Customer::find($personalAccessToken->tokenable_id);

            if (! $customer || $customer->is_suspended) {
                return null;
            }

            return $customer->id;
        } catch (\Exception) {
            return null;
        }
    }
}
