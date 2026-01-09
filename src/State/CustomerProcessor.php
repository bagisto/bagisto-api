<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Models\Customer;
use Webkul\BagistoApi\Validators\CustomerValidator;
use Webkul\Customer\Repositories\CustomerRepository;

class CustomerProcessor implements ProcessorInterface
{
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerValidator $validator
    ) {}

    /**
     * Process the customer creation or update operation.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Customer
    {
        if ($data instanceof Customer) {
            if ($operation->getName() === '_api_/api/shop/customers{._format}_post' || str_contains($operation->getName(), 'post') || $operation->getName() === 'create') {

                try {
                    $this->validator->validateForCreation($data);
                } catch (\Exception $e) {
                    throw $e;
                }

                if (! empty($data->password) && ! empty($data->confirm_password)) {
                    if ($data->password !== $data->confirm_password) {
                        throw new \InvalidArgumentException(__('bagistoapi::app.graphql.customer.password-mismatch'));
                    }
                }

                if (! empty($data->password) && ! Hash::isHashed($data->password)) {
                    $data->password = Hash::make($data->password);
                }

                $customerData = [
                    'first_name'                => $data->first_name,
                    'last_name'                 => $data->last_name,
                    'email'                     => $data->email,
                    'password'                  => $data->password,
                    'phone'                     => $data->phone,
                    'gender'                    => $data->gender,
                    'date_of_birth'             => $data->date_of_birth,
                    'status'                    => $data->status ?? 1,
                    'is_verified'               => $data->is_verified ?? 0,
                    'is_suspended'              => $data->is_suspended ?? 0,
                    'subscribed_to_news_letter' => $data->subscribed_to_news_letter ?? false,
                    'api_token'                 => Str::random(80),
                    'token'                     => md5(uniqid(rand(), true)),
                ];

                Event::dispatch('customer.registration.before');

                $customer = $this->customerRepository->create($customerData);

                Event::dispatch('customer.create.after', $customer);

                Event::dispatch('customer.registration.after', $customer);

                $freshCustomer = Customer::findOrFail($customer->id);

                return $freshCustomer;
            } elseif ($operation->getName() === 'update') {

                $this->requireAuthentication();

                Event::dispatch('customer.update.before', $data);

                $passwordWasChanged = isset($data->password) && $data->password !== $data->getOriginal('password');

                if ($passwordWasChanged) {
                    if (! isset($data->confirm_password) || empty($data->confirm_password)) {
                        throw new \InvalidArgumentException(__('bagistoapi::app.graphql.customer.confirm-password-required'));
                    }
                    if ($data->password !== $data->confirm_password) {
                        throw new \InvalidArgumentException(__('bagistoapi::app.graphql.customer.password-mismatch'));
                    }
                    if (! Hash::isHashed($data->password)) {
                        $data->password = Hash::make($data->password);
                    }
                } else {
                    unset($data->password);
                    unset($data->confirm_password);
                }

                $this->validator->validateForUpdate($data);

                $data->save();

                Event::dispatch('customer.update.after', $data);

                return $data;
            } elseif ($operation->getName() === 'delete') {

                $this->requireAuthentication();

                Event::dispatch('customer.delete.before', $data);

                $data->delete();

                Event::dispatch('customer.delete.after', $data);

                return $data;
            }
        }

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
