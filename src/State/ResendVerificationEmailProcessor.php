<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Dto\ResendVerificationEmailInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\ResendVerificationEmail;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Shop\Mail\Customer\EmailVerificationNotification;

class ResendVerificationEmailProcessor implements ProcessorInterface
{
    /**
     * Requests allowed per minute and IP, as core's resend route allows.
     */
    private const MAX_ATTEMPTS = 6;

    /**
     * Create a new processor instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Issue a fresh verification token and email it, answering alike whether or not the address is registered.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $key = 'verification-email:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.rate-limit.too-many-attempts', ['seconds' => RateLimiter::availableIn($key)]), 429);
        }

        RateLimiter::hit($key, 60);

        $defaultResponse = [
            'success' => false,
            'message' => '',
        ];

        $isRestPost = $operation instanceof Post;
        $isGraphQlCreate = $operation->getName() === 'create';

        if (
            ! $isRestPost
            && ! $isGraphQlCreate
        ) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.forgot-password.invalid-operation');

            return $this->output($defaultResponse);
        }

        if (
            $isRestPost
            && ! $data instanceof ResendVerificationEmailInput
        ) {
            $input = new ResendVerificationEmailInput;
            $input->email = (string) (request()->input('email') ?? '');
            $data = $input;
        }

        if (! ($data instanceof ResendVerificationEmailInput)) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.forgot-password.invalid-input-data');

            return $this->output($defaultResponse);
        }

        $validator = Validator::make(['email' => $data->email ?? ''], [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return $this->output([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $customer = $this->customerRepository->findOneByField('email', $data->email);

        if (! $customer) {
            return $this->output([
                'success' => true,
                'message' => trans('shop::app.customers.signup-form.verification-sent'),
            ]);
        }

        $customer = $this->customerRepository->update([
            'token' => Str::random(64),
        ], $customer->id);

        try {
            Mail::queue(new EmailVerificationNotification($customer));
        } catch (\Throwable $exception) {
            report($exception);

            return $this->output([
                'success' => false,
                'message' => trans('shop::app.customers.signup-form.verification-not-sent'),
            ]);
        }

        return $this->output([
            'success' => true,
            'message' => trans('shop::app.customers.signup-form.verification-sent'),
        ]);
    }

    /**
     * Build the response object from the result.
     */
    private function output(array $data): ResendVerificationEmail
    {
        $output = new ResendVerificationEmail;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }
}
