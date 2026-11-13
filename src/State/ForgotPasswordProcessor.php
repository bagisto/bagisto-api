<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Password;
use Webkul\BagistoApi\Dto\ForgotPasswordInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\ForgotPassword;

class ForgotPasswordProcessor implements ProcessorInterface
{
    /**
     * Send a reset link to a customer of the current channel, answering the same way whether or not the email is known.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ForgotPassword
    {
        $isRestPost = $operation instanceof Post;

        if (
            ! $isRestPost
            && $operation->getName() !== 'create'
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.forgot-password.invalid-operation'));
        }

        if (
            $isRestPost
            && ! $data instanceof ForgotPasswordInput
        ) {
            $input = new ForgotPasswordInput;
            $input->email = (string) (request()->input('email') ?? '');
            $data = $input;
        }

        if (! $data instanceof ForgotPasswordInput) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.forgot-password.invalid-input-data'));
        }

        if (empty($data->email)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.forgot-password.email-required'), 422);
        }

        $this->sendResetLink($data->email);

        $output = new ForgotPassword;
        $output->success = true;
        $output->message = __('bagistoapi::app.graphql.forgot-password.reset-link-sent');

        return $output;
    }

    /**
     * Ask the customers broker for a reset link, reporting a delivery failure without revealing it.
     */
    private function sendResetLink(string $email): void
    {
        try {
            Password::broker('customers')->sendResetLink([
                'email' => $email,
                'channel_id' => core()->getCurrentChannel()->id,
            ]);
        } catch (\Exception $exception) {
            report($exception);
        }
    }
}
