<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\Customer as ApiCustomer;
use Webkul\BagistoApi\Support\DateOfBirth;
use Webkul\Customer\Models\Customer;

class CustomerProfileProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = Request::instance() ?? ($context['request'] ?? null);

        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.customer-profile.authentication-required'));
        }

        $authenticatedCustomer = $this->getCustomerFromToken($token);

        if (! $authenticatedCustomer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.customer-profile.invalid-token'));
        }

        return [
            'id' => (string) $authenticatedCustomer->id,
            'firstName' => $authenticatedCustomer->first_name,
            'lastName' => $authenticatedCustomer->last_name,
            'email' => $authenticatedCustomer->email,
            'phone' => $authenticatedCustomer->phone,
            'gender' => $authenticatedCustomer->gender,
            'dateOfBirth' => DateOfBirth::format($authenticatedCustomer->date_of_birth),
            'status' => $authenticatedCustomer->status,
            'subscribedToNewsLetter' => $authenticatedCustomer->subscribed_to_news_letter,
            'isVerified' => (string) $authenticatedCustomer->is_verified,
            'isSuspended' => (string) $authenticatedCustomer->is_suspended,
        ];
    }

    /**
     * Get customer from Sanctum token.
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

            return Customer::find($personalAccessToken->tokenable_id);
        } catch (\Exception $exception) {
            return null;
        }
    }
}
