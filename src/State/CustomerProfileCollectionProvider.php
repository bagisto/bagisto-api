<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\CustomerProfile;
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
        $profile->date_of_birth = $authenticatedCustomer->date_of_birth;
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
            $tokenParts = explode('|', $token);

            if (count($tokenParts) !== 2) {
                return null;
            }

            $tokenId = $tokenParts[0];

            $personalAccessToken = DB::table('personal_access_tokens')
                ->where('id', $tokenId)
                ->whereIn('tokenable_type', [Customer::class, \Webkul\BagistoApi\Models\Customer::class])
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
