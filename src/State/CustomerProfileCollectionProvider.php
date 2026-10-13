<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\Customer as ApiCustomer;
use Webkul\BagistoApi\Models\CustomerProfile;
use Webkul\BagistoApi\Support\DateOfBirth;
use Webkul\Customer\Models\Customer;

/**
 * REST provider for authenticated customer profile.
 * Returns the profile as a single-item collection for the GetCollection operation.
 */
class CustomerProfileCollectionProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
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

        $profile = new CustomerProfile;
        $profile->id = (string) $authenticatedCustomer->id;
        $profile->first_name = $authenticatedCustomer->first_name;
        $profile->last_name = $authenticatedCustomer->last_name;
        $profile->email = $authenticatedCustomer->email;
        $profile->phone = $authenticatedCustomer->phone;
        $profile->gender = $authenticatedCustomer->gender;
        $profile->date_of_birth = DateOfBirth::format($authenticatedCustomer->date_of_birth);
        $profile->status = $authenticatedCustomer->status;
        $profile->subscribed_to_news_letter = $authenticatedCustomer->subscribed_to_news_letter;
        $profile->is_verified = (string) $authenticatedCustomer->is_verified;
        $profile->is_suspended = (string) $authenticatedCustomer->is_suspended;
        $profile->image = $authenticatedCustomer->image;

        return [$profile];
    }

    private function getCustomerFromToken(string $token): ?Customer
    {
        try {
            $personalAccessToken = PersonalAccessToken::findToken($token);

            if (
                ! $personalAccessToken
                || ! in_array($personalAccessToken->tokenable_type, [Customer::class, ApiCustomer::class], true)
                || ($personalAccessToken->expires_at && $personalAccessToken->expires_at->isPast())
            ) {
                return null;
            }

            return Customer::find($personalAccessToken->tokenable_id);
        } catch (\Exception $exception) {
            return null;
        }
    }
}
