<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Dto\ResetPasswordInput;
use Webkul\BagistoApi\Models\ResetPassword;
use Webkul\Core\Helpers\SessionPurger;
use Webkul\Customer\Repositories\CustomerRepository;

class ResetPasswordProcessor implements ProcessorInterface
{
    /**
     * Create a new processor instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Reset a customer's password on the current channel with the emailed token, signing out every
     * session and revoking every API token the customer holds, as core's reset does.
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
            && ! $data instanceof ResetPasswordInput
        ) {
            $input = new ResetPasswordInput;
            $input->token = (string) (request()->input('token') ?? '');
            $input->email = (string) (request()->input('email') ?? '');
            $input->password = (string) (request()->input('password') ?? '');
            $input->password_confirmation = (string) (request()->input('password_confirmation') ?? '');
            $data = $input;
        }

        if (! ($data instanceof ResetPasswordInput)) {
            $defaultResponse['message'] = __('bagistoapi::app.graphql.forgot-password.invalid-input-data');

            return $this->output($defaultResponse);
        }

        $passwordConfirmation = $data->password_confirmation ?? '';

        $dataArray = [
            'token' => $data->token ?? '',
            'email' => $data->email ?? '',
            'password' => $data->password ?? '',
            'password_confirmation' => $passwordConfirmation,
        ];

        $validator = Validator::make($dataArray, [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'password_confirmation' => ['required', 'same:password'],
        ]);

        if ($validator->fails()) {
            return $this->output([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            $credentials = [
                'token' => $data->token,
                'email' => $data->email,
                'password' => $data->password,
                'password_confirmation' => $passwordConfirmation,
                'channel_id' => core()->getCurrentChannel()->id,
            ];

            $response = $this->broker()->reset($credentials, function ($customer, $password) {
                $customer->password = Hash::make($password);
                $customer->setRememberToken(Str::random(60));
                $customer->save();

                $customer->tokens()->delete();

                app(SessionPurger::class)->forget('customer', $customer->id);

                event(new PasswordReset($customer));
            });

            if ($response == Password::PASSWORD_RESET) {
                $customer = $this->customerRepository->findOneWhere([
                    'email' => $data->email,
                    'channel_id' => core()->getCurrentChannel()->id,
                ]);

                if ($customer) {
                    Event::dispatch('customer.password.update.after', $customer);
                }

                return $this->output([
                    'success' => true,
                    'message' => trans($response),
                ]);
            }

            return $this->output([
                'success' => false,
                'message' => trans($response),
            ]);
        } catch (\Exception $exception) {
            report($exception);

            return $this->output([
                'success' => false,
                'message' => __('bagistoapi::app.graphql.reset-password.failed'),
            ]);
        }
    }

    /**
     * Get the password broker for customers.
     */
    private function broker()
    {
        return Password::broker('customers');
    }

    /**
     * Build the response object from the result.
     */
    private function output(array $data): ResetPassword
    {
        $output = new ResetPassword;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }
}
