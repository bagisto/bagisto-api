<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Dto\VerifyTokenInput;
use Webkul\BagistoApi\Models\VerifyToken;
use Webkul\BagistoApi\Support\CurrentCustomer;

class VerifyTokenProcessor implements ProcessorInterface
{
    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $defaultResponse = [
            'id' => 0,
            'firstName' => '',
            'lastName' => '',
            'email' => '',
            'isValid' => false,
            'message' => '',
        ];

        $isRestPost = $operation instanceof Post;
        $isGraphQlCreate = $operation->getName() === 'create';

        if (
            ! $isRestPost
            && ! $isGraphQlCreate
        ) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.invalid-operation');

            return $this->output($defaultResponse);
        }

        if (
            $isRestPost
            && ! $data instanceof VerifyTokenInput
        ) {
            $data = new VerifyTokenInput;
        }

        if (! ($data instanceof VerifyTokenInput)) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.invalid-input-data');

            return $this->output($defaultResponse);
        }

        $customer = CurrentCustomer::user();

        if (! $customer) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.unauthenticated');

            return $this->output($defaultResponse);
        }

        try {
            $session = CurrentCustomer::session();

            if (! $session) {
                $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.token-not-found-or-expired');

                return $this->output($defaultResponse);
            }

            if ($customer->is_suspended) {
                $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.customer-account-suspended');

                return $this->output($defaultResponse);
            }

            return $this->output([
                'id' => $customer->id,
                'firstName' => $customer->first_name,
                'lastName' => $customer->last_name,
                'email' => $customer->email,
                'isValid' => true,
                'sessionId' => (int) $session->id,
                'sessionExpiresAt' => $session->expires_at?->toIso8601String(),
                'message' => __('bagistoapi::app.graphql.token-verification.token-is-valid'),
            ]);

        } catch (\Exception $exception) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.token-verification.error-verifying-token');

            return $this->output($defaultResponse);
        }
    }

    /**
     * Output.
     */
    private function output(array $data): VerifyToken
    {
        $output = new VerifyToken;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }
}
