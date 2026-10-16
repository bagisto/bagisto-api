<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Dto\VerifyAccountInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\VerifyAccount;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Shop\Mail\Customer\RegistrationNotification;

/**
 * Mirrors the storefront RegistrationController::verifyAccount.
 */
class VerifyAccountProcessor implements ProcessorInterface
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): VerifyAccount
    {
        $token = $data instanceof VerifyAccountInput ? $data->token : null;
        $token ??= $context['args']['input']['token'] ?? request()->input('token');

        if (! is_string($token) || trim($token) === '') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.verify-account.token-required'), 422);
        }

        $customer = $this->customerRepository->findOneByField('token', $token);

        if (! $customer) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.verify-account.invalid-token'), 422);
        }

        $this->customerRepository->update([
            'is_verified' => 1,
            'token' => null,
        ], $customer->id);

        if ((bool) core()->getConfigData('emails.general.notifications.emails.general.notifications.registration')) {
            Mail::queue(new RegistrationNotification($customer));
        }

        $this->customerRepository->syncNewRegisteredCustomerInformation($customer);

        $result = new VerifyAccount;
        $result->success = true;
        $result->message = __('bagistoapi::app.graphql.verify-account.verified');

        return $result;
    }
}
