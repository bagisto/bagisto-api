<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Dto\ResetPasswordInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\ResetPassword;
use Webkul\Customer\Repositories\CustomerRepository;

/**
 * Mirrors the storefront ResetPasswordController::store over the customers password broker.
 */
class ResetPasswordProcessor implements ProcessorInterface
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ResetPassword
    {
        $input = $this->resolveInput($data, $context);

        $validator = Validator::make($input, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        $response = Password::broker('customers')->reset($input, function ($customer, string $password) {
            $customer->password = Hash::make($password);

            $customer->setRememberToken(Str::random(60));

            $customer->save();

            event(new PasswordReset($customer));
        });

        if ($response !== Password::PASSWORD_RESET) {
            throw new InvalidInputException(trans($response), 422);
        }

        Event::dispatch('customer.password.update.after', $this->customerRepository->findOneByField('email', $input['email']));

        $result = new ResetPassword;
        $result->success = true;
        $result->message = __('bagistoapi::app.graphql.reset-password.success');

        return $result;
    }

    /**
     * @return array{token: ?string, email: ?string, password: ?string, password_confirmation: ?string}
     */
    protected function resolveInput(mixed $data, array $context): array
    {
        $args = $context['args']['input'] ?? [];
        $dto = $data instanceof ResetPasswordInput ? $data : null;

        $pick = function (string $snake, string $camel) use ($dto, $args): ?string {
            $value = $dto?->{$snake} ?? $args[$camel] ?? $args[$snake] ?? request()->input($camel) ?? request()->input($snake);

            return is_scalar($value) ? (string) $value : null;
        };

        return [
            'token' => $pick('token', 'token'),
            'email' => $pick('email', 'email'),
            'password' => $pick('password', 'password'),
            'password_confirmation' => $pick('password_confirmation', 'passwordConfirmation'),
        ];
    }
}
