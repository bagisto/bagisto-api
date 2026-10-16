<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\VerifyAccountInput;
use Webkul\BagistoApi\Models\VerifyAccount;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Shop\Mail\Customer\RegistrationNotification;

class VerifyAccountProcessor implements ProcessorInterface
{
    /**
     * Create a new processor instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Verify a customer's account with the emailed token.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
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
            && ! $data instanceof VerifyAccountInput
        ) {
            $input = new VerifyAccountInput;
            $input->token = (string) (request()->input('token') ?? '');
            $data = $input;
        }

        if (! ($data instanceof VerifyAccountInput)) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.forgot-password.invalid-input-data');

            return $this->output($defaultResponse);
        }

        $validator = Validator::make(['token' => $data->token ?? ''], [
            'token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->output([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $customer = $this->customerRepository->findOneByField('token', $data->token);

        if (! $customer) {
            return $this->output([
                'success' => false,
                'message' => trans('shop::app.customers.signup-form.verify-failed'),
            ]);
        }

        $this->customerRepository->update([
            'is_verified' => 1,
            'token' => null,
        ], $customer->id);

        if ((bool) core()->getConfigData('emails.general.notifications.emails.general.notifications.registration')) {
            try {
                Mail::queue(new RegistrationNotification($customer));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->customerRepository->syncNewRegisteredCustomerInformation($customer);

        return $this->output([
            'success' => true,
            'message' => trans('shop::app.customers.signup-form.verified'),
        ]);
    }

    /**
     * Build the response object from the result.
     */
    private function output(array $data): VerifyAccount
    {
        $output = new VerifyAccount;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }
}
