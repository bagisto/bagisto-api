<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Password;
use Webkul\BagistoApi\Dto\ForgotPasswordInput;

class ForgotPasswordProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $defaultResponse = [
            'success' => false,
            'message' => '',
        ];

        if ($operation->getName() !== 'create') {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.forgot-password.invalid-operation');

            return (object) $defaultResponse;
        }

        if (! ($data instanceof ForgotPasswordInput)) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.forgot-password.invalid-input-data');

            return (object) $defaultResponse;
        }

        if (empty($data->email)) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.forgot-password.email-required');

            return (object) $defaultResponse;
        }

        try {
            $response = $this->broker()->sendResetLink(['email' => $data->email]);

            if ($response == Password::RESET_LINK_SENT) {
                return (object) [
                    'success' => true,
                    'message' => __('bagistoapi::app.graphql.forgot-password.reset-link-sent'),
                ];
            }

            return (object) [
                'success' => false,
                'message' => __('bagistoapi::app.graphql.forgot-password.email-not-found'),
            ];

        } catch (\Exception $e) {
            return (object) [
                'success' => false,
                'message' => __('bagistoapi::app.graphql.forgot-password.error-sending-reset-link'),
            ];
        }
    }

    private function broker()
    {
        return Password::broker('customers');
    }
}
