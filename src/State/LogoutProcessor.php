<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;

class LogoutProcessor implements ProcessorInterface
{
    public function __construct() {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        // Try Sanctum guard first (GraphQL), then manual token extraction (REST)
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            $request = Request::instance() ?? ($context['request'] ?? null);
            $bearerToken = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

            if ($bearerToken) {
                $tokenParts = explode('|', $bearerToken);
                if (count($tokenParts) === 2) {
                    $personalAccessToken = DB::table('personal_access_tokens')
                        ->where('id', $tokenParts[0])
                        ->whereIn('tokenable_type', [\Webkul\Customer\Models\Customer::class, \Webkul\BagistoApi\Models\Customer::class])
                        ->first();

                    if ($personalAccessToken) {
                        $customer = \Webkul\Customer\Models\Customer::find($personalAccessToken->tokenable_id);
                        // Set the token on the customer so currentAccessToken() works
                        $customer->withAccessToken(
                            \Laravel\Sanctum\PersonalAccessToken::find($personalAccessToken->id)
                        );
                    }
                }
            }
        }

        if (! $customer) {
            return (object) [
                'success' => false,
                'message' => __('bagistoapi::app.graphql.logout.unauthenticated'),
            ];
        }

        try {

            $token = $customer->currentAccessToken();

            if (! $token) {

                return (object) [
                    'success' => false,
                    'message' => __('bagistoapi::app.graphql.logout.token-not-found-or-expired'),
                ];
            }

            // Dispatch event to delete device_token - PushNotification package will handle this
            $deviceToken = $data->deviceToken ?? null;
            if ($deviceToken) {
                Event::dispatch('bagistoapi.customer.device-token.delete', [
                    'customerId'  => $customer->id,
                    'deviceToken' => $deviceToken,
                ]);
            }

            // Clear device_token if column exists (added by PushNotification plugin)
            if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'device_token')) {
                $customer->forceFill(['device_token' => null]);
                $customer->save();
            }

            $token->delete();

            return (object) [
                'success' => true,
                'message' => __('bagistoapi::app.graphql.logout.logged-out-successfully'),
            ];

        } catch (\Exception $e) {
            return (object) [
                'success' => false,
                'message' => __('bagistoapi::app.graphql.logout.error-during-logout'),
            ];
        }
    }
}
