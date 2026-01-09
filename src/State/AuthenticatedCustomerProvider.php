<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
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

            return Customer::find($personalAccessToken->tokenable_id);
        } catch (\Exception $e) {
            return null;
        }
    }
}
