<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\Customer as ApiCustomer;
use Webkul\Customer\Models\Customer;

class AuthenticatedCustomerProvider implements ProviderInterface
{
    /**
     * Fetch authenticated customer from Sanctum token.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Customer
    {
        $input = $context['args']['input'] ?? null;
        $request = Request::instance() ?? ($context['request'] ?? null);

        // Extract token from Authorization header only (no input property)
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
     * Recommended format: Authorization: Bearer <customer_token>
     */
    private function extractTokenFromRequest($request): ?string
    {
        return TokenHeaderFacade::getAuthorizationBearerToken($request);
    }

    /**
     * Retrieve customer from Sanctum personal access token.
     */
    private function getCustomerFromToken(string $token): ?Customer
    {
        try {
            $personalAccessToken = PersonalAccessToken::findToken($token);

            if (
                ! $personalAccessToken
                || ! in_array($personalAccessToken->tokenable_type, [Customer::class, ApiCustomer::class], true)
                || (
                    $personalAccessToken->expires_at
                    && $personalAccessToken->expires_at->isPast()
                )
            ) {
                return null;
            }

            return ApiCustomer::find($personalAccessToken->tokenable_id);
        } catch (\Exception $exception) {
            return null;
        }
    }
}
