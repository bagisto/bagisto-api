<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\Customer;
use Webkul\BagistoApi\Validators\CustomerValidator;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;

class CustomerProcessor implements ProcessorInterface
{
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerValidator $validator,
        protected CustomerGroupRepository $customerGroupRepository
    ) {}

    /**
     * Resolve the default customer group id the storefront assigns at registration.
     */
    private function defaultCustomerGroupId(): ?int
    {
        $code = core()->getConfigData('customer.settings.create_new_account_options.default_group') ?: 'general';

        return $this->customerGroupRepository->findOneWhere(['code' => $code])?->id;
    }

    /**
     * Process the customer creation or update operation.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Customer
    {
        if (! $data instanceof Customer) {
            return $data;
        }

        $operationName = $operation->getName();

        if (str_contains($operationName, 'post') || $operationName === 'create') {
            return $this->createCustomer($data);
        }

        if ($operationName === 'update') {
            return $this->updateCustomer($data);
        }

        if ($operationName === 'delete') {
            return $this->deleteCustomer($data);
        }

        return $data;
    }

    private function createCustomer(Customer $data): Customer
    {
        $this->validator->validateForCreation($data);

        $normalizedGender = $this->validator->validateGender($data->gender ?? null);

        if ($normalizedGender !== null) {
            $data->gender = $normalizedGender;
        }

        if (
            ! empty($data->password)
            && ! empty($data->confirm_password)
            && $data->password !== $data->confirm_password
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.password-mismatch'));
        }

        if (! empty($data->password) && ! Hash::isHashed($data->password)) {
            $data->password = Hash::make($data->password);
        }

        $customerData = [
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'email' => $data->email,
            'password' => $data->password,
            'phone' => $data->phone,
            'gender' => $data->gender,
            'date_of_birth' => $data->date_of_birth,
            'status' => 1,
            'is_verified' => ! core()->getConfigData('customer.settings.email.verification'),
            'is_suspended' => 0,
            'subscribed_to_news_letter' => $data->subscribed_to_news_letter ?? false,
            'api_token' => Str::random(80),
            'token' => md5(uniqid(rand(), true)),
            'channel_id' => core()->getCurrentChannel()->id,
            'customer_group_id' => $this->defaultCustomerGroupId(),
        ];

        Event::dispatch('customer.registration.before');

        $customer = $this->customerRepository->create($customerData);

        $deviceToken = $data->device_token ?? $data->deviceToken ?? null;

        if ($deviceToken) {
            Event::dispatch('bagistoapi.customer.device-token.save', [
                'customerId' => $customer->id,
                'deviceToken' => $deviceToken,
            ]);
        }

        Event::dispatch('customer.create.after', $customer);

        Event::dispatch('customer.registration.after', $customer);

        $freshCustomer = Customer::findOrFail($customer->id);

        if (
            core()->getConfigData('customer.settings.email.verification')
            && ! $freshCustomer->is_verified
        ) {
            $freshCustomer->token = null;
            $freshCustomer->message = __('bagistoapi::app.graphql.customer.verify-email-to-login');

            return $freshCustomer;
        }

        $freshCustomer->token = $freshCustomer->createToken('customer-registration')->plainTextToken;

        return $freshCustomer;
    }

    private function updateCustomer(Customer $data): Customer
    {
        $this->requireAuthentication();

        Event::dispatch('customer.update.before', $data);

        $this->applyPasswordChange($data);

        $this->validator->validateForUpdate($data);

        if (isset($data->gender)) {
            $data->gender = $this->validator->validateGender($data->gender);
        }

        $data->save();

        Event::dispatch('customer.update.after', $data);

        return $data;
    }

    /**
     * Validate and hash a changed password, or drop the password fields when it did not change.
     */
    private function applyPasswordChange(Customer $data): void
    {
        $passwordWasChanged = isset($data->password) && $data->password !== $data->getOriginal('password');

        if (! $passwordWasChanged) {
            unset($data->password, $data->confirm_password);

            return;
        }

        if (empty($data->confirm_password)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.confirm-password-required'));
        }

        if ($data->password !== $data->confirm_password) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.password-mismatch'));
        }

        if (! Hash::isHashed($data->password)) {
            $data->password = Hash::make($data->password);
        }
    }

    private function deleteCustomer(Customer $data): Customer
    {
        $this->requireAuthentication();

        Event::dispatch('customer.delete.before', $data);

        $data->delete();

        Event::dispatch('customer.delete.after', $data);

        return $data;
    }

    /**
     * Verify that the customer is authenticated.
     */
    private function requireAuthentication(): void
    {
        if (! Auth::guard('sanctum')->check()) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.customer.unauthenticated'));
        }
    }
}
