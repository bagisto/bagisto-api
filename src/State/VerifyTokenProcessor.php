<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Dto\VerifyTokenInput;

class VerifyTokenProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $defaultResponse = [
            'id'        => 0,
            'firstName' => '',
            'lastName'  => '',
            'email'     => '',
            'isValid'   => false,
            'message'   => '',
        ];

        if ($operation->getName() !== 'create') {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.invalid-operation');

            return (object) $defaultResponse;
        }

        if (! ($data instanceof VerifyTokenInput)) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.invalid-input-data');

            return (object) $defaultResponse;
        }

        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.unauthenticated');

            return (object) $defaultResponse;
        }

        try {
            $token = $customer->currentAccessToken();

            if (! $token) {
                $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.token-not-found-or-expired');

                return (object) $defaultResponse;
            }

            if ($customer->is_suspended) {
                $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.customer-account-suspended');

                return (object) $defaultResponse;
            }

            return (object) [
                'id'        => $customer->id,
                'firstName' => $customer->first_name,
                'lastName'  => $customer->last_name,
                'email'     => $customer->email,
                'isValid'   => true,
                'message'   => __('bagistoapi::app.graphql.token-verification.token-is-valid'),
            ];

        } catch (\Exception $e) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.error-verifying-token');

            return (object) $defaultResponse;
        }
    }
}
