<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;

class LogoutProcessor implements ProcessorInterface
{
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
