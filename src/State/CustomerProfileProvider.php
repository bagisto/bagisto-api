<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\Customer\Models\Customer;

/**
 * Fetch customer profile via query.
 */
class CustomerProfileProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        // Extract Bearer token from Authorization header
        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.customer-profile.authentication-required'));
        }

        $authenticatedCustomer = $this->getCustomerFromToken($token);

        if (! $authenticatedCustomer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.customer-profile.invalid-token'));
        }

        // Map customer data to return array
        return [
            'id'                     => (string) $authenticatedCustomer->id,
            'firstName'              => $authenticatedCustomer->first_name,
            'lastName'               => $authenticatedCustomer->last_name,
            'email'                  => $authenticatedCustomer->email,
            'phone'                  => $authenticatedCustomer->phone,
            'gender'                 => $authenticatedCustomer->gender,
            'dateOfBirth'            => $authenticatedCustomer->date_of_birth,
            'status'                 => $authenticatedCustomer->status,
            'subscribedToNewsLetter' => $authenticatedCustomer->subscribed_to_news_letter,
            'isVerified'             => (string) $authenticatedCustomer->is_verified,
            'isSuspended'            => (string) $authenticatedCustomer->is_suspended,
        ];
    }

    /**
     * Get customer from Sanctum token.
     */
    private function getCustomerFromToken(string $token): ?Customer
    {
        try {
            $tokenParts = explode('|', $token);

            if (count($tokenParts) !== 2) {
                return null;
            }

            $tokenId = $tokenParts[0];

            $personalAccessToken = \Illuminate\Support\Facades\DB::table('personal_access_tokens')
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
