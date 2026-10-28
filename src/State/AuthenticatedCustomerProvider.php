<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\Customer as ApiCustomer;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Customer\Models\Customer;

class AuthenticatedCustomerProvider implements ProviderInterface
{
    /**
     * Fetch the customer behind the customer access token.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Customer
    {
        $input = $context['args']['input'] ?? null;
        $request = Request::instance() ?? ($context['request'] ?? null);

        $token = $this->extractTokenFromRequest($request);

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.no-token-provided'));
        }

        $customer = $this->getCustomerFromToken($token);

        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.invalid-or-expired-token'));
        }

        return $customer;
    }

    /**
     * Extract Bearer token from Authorization header.
     * Recommended format: Authorization: Bearer <customer_token>.
     */
    private function extractTokenFromRequest($request): ?string
    {
        return TokenHeaderFacade::getAuthorizationBearerToken($request);
    }

    /**
     * Resolve the customer behind a customer access token.
     */
    private function getCustomerFromToken(string $token): ?Customer
    {
        $customer = CurrentCustomer::fromToken($token);

        return $customer ? (new ApiCustomer)->newFromBuilder($customer->getAttributes()) : null;
    }
}
