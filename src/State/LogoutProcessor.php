<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\Customer as ApiCustomer;
use Webkul\BagistoApi\Models\CustomerLogout;
use Webkul\Customer\Models\Customer;

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

            $personalAccessToken = $bearerToken ? PersonalAccessToken::findToken($bearerToken) : null;

            if (
                $personalAccessToken
                && in_array($personalAccessToken->tokenable_type, [Customer::class, ApiCustomer::class], true)
                && ! ($personalAccessToken->expires_at && $personalAccessToken->expires_at->isPast())
            ) {
                $customer = Customer::find($personalAccessToken->tokenable_id);

                $customer?->withAccessToken($personalAccessToken);
            }
        }

        if (! $customer) {
            return $this->output([
                'success' => false,
                'message' => __('bagistoapi::app.graphql.logout.unauthenticated'),
            ]);
        }

        try {

            $token = $customer->currentAccessToken();

            if (! $token) {

                return $this->output([
                    'success' => false,
                    'message' => __('bagistoapi::app.graphql.logout.token-not-found-or-expired'),
                ]);
            }

            // Dispatch event to delete device_token - PushNotification package will handle this
            $deviceToken = $data->deviceToken ?? null;
            if ($deviceToken) {
                Event::dispatch('bagistoapi.customer.device-token.delete', [
                    'customerId' => $customer->id,
                    'deviceToken' => $deviceToken,
                ]);
            }

            // Clear device_token if column exists (added by PushNotification plugin)
            if (Schema::hasColumn('customers', 'device_token')) {
                $customer->forceFill(['device_token' => null]);
                $customer->save();
            }

            $token->delete();

            Event::dispatch('customer.after.logout', $customer->id);

            return $this->output([
                'success' => true,
                'message' => __('bagistoapi::app.graphql.logout.logged-out-successfully'),
            ]);

        } catch (\Exception $e) {
            return $this->output([
                'success' => false,
                'message' => __('bagistoapi::app.graphql.logout.error-during-logout'),
            ]);
        }
    }

    private function output(array $data): CustomerLogout
    {
        $output = new CustomerLogout;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }
}
