<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\VerificationEmailInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\VerificationEmail;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Shop\Mail\Customer\EmailVerificationNotification;

/**
 * Mirrors the storefront RegistrationController::resendVerificationEmail.
 */
class VerificationEmailProcessor implements ProcessorInterface
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): VerificationEmail
    {
        $email = $data instanceof VerificationEmailInput ? $data->email : null;
        $email ??= $context['args']['input']['email'] ?? request()->input('email');

        $validator = Validator::make(['email' => $email], ['email' => 'required|email']);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        if ($customer = $this->customerRepository->findOneByField('email', $email)) {
            $customer = $this->customerRepository->update(['token' => md5(uniqid(rand(), true))], $customer->id);

            try {
                Mail::queue(new EmailVerificationNotification($customer));
            } catch (\Exception $e) {
                report($e);

                throw new InvalidInputException(__('bagistoapi::app.graphql.verification-email.not-sent'), 500);
            }
        }

        $result = new VerificationEmail;
        $result->success = true;
        $result->message = __('bagistoapi::app.graphql.verification-email.sent');

        return $result;
    }
}
