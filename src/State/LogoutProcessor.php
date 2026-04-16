<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class LogoutProcessor implements ProcessorInterface
{
    public function __construct() {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $customer = Auth::guard('sanctum')->user();

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

            // Also clear the old device_token column from customers table for backward compatibility
            $customer->forceFill(['device_token' => null]);
            $customer->save();

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
