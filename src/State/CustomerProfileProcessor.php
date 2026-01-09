<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Helper\CustomerProfileHelper;
use Webkul\BagistoApi\Validators\CustomerValidator;
use Webkul\Customer\Models\Customer;

class CustomerProfileProcessor implements ProcessorInterface
{
    public function __construct(
        protected CustomerValidator $validator
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer instanceof Customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.invalid-or-expired-token'));
        }

        $resourceClass = $operation->getClass();

        $resourceShortName = class_basename($resourceClass);

        if ($resourceShortName === 'CustomerProfileDelete') {
            return $this->handleDelete($customer);
        } elseif ($resourceShortName === 'CustomerProfileUpdate') {
            return $this->handleUpdate($data, $customer);
        }

        throw new \InvalidArgumentException(__('bagistoapi::app.graphql.auth.unknown-resource'));
    }

    /**
     * Handle customer profile update.
     */
    private function handleUpdate(mixed $data, Customer $customer)
    {
        $updateData = [];

        if (is_object($data) && property_exists($data, 'id') && $data->id) {
            if ((int) $data->id !== (int) $customer->id) {
                throw new AuthenticationException(__('bagistoapi::app.graphql.auth.cannot-update-other-profile'));
            }
        }

        if (is_object($data) && property_exists($data, 'firstName') && ! empty($data->firstName)) {
            $updateData['first_name'] = $data->firstName;
        }

        if (is_object($data) && property_exists($data, 'lastName') && ! empty($data->lastName)) {
            $updateData['last_name'] = $data->lastName;
        }

        if (is_object($data) && property_exists($data, 'email') && ! empty($data->email)) {
            $updateData['email'] = $data->email;
        }

        if (is_object($data) && property_exists($data, 'phone') && ! empty($data->phone)) {
            $updateData['phone'] = $data->phone;
        }

        if (is_object($data) && property_exists($data, 'gender') && ! empty($data->gender)) {
            $updateData['gender'] = $data->gender;
        }

        if (is_object($data) && property_exists($data, 'dateOfBirth') && ! empty($data->dateOfBirth)) {
            $updateData['date_of_birth'] = $data->dateOfBirth;
        }

        if (is_object($data) && property_exists($data, 'password') && ! empty($data->password)) {
            if (is_object($data) && property_exists($data, 'confirmPassword')) {
                if ($data->password !== $data->confirmPassword) {
                    throw new \InvalidArgumentException(__('bagistoapi::app.graphql.customer.password-mismatch'));
                }
            }
            if (! Hash::isHashed($data->password)) {
                $updateData['password'] = Hash::make($data->password);
            }
        }

        if (is_object($data) && property_exists($data, 'subscribedToNewsLetter')) {
            $updateData['subscribed_to_news_letter'] = $data->subscribedToNewsLetter;
        }

        // Validate customer data using CustomerValidator
        // Update customer attributes with new values before validation
        if (! empty($updateData)) {
            $customer->fill($updateData);
        }

        $this->validator->validateForUpdate($customer);

        Event::dispatch('customer.update.before');

        if (! empty($updateData)) {
            $customer->update($updateData);
        }

        if (is_object($data) && property_exists($data, 'deleteImage') && $data->deleteImage) {
            if ($customer->image) {
                Storage::delete($customer->image);
                $customer->update(['image' => null]);
            }
        } elseif (is_object($data) && property_exists($data, 'image') && ! empty($data->image)) {
            CustomerProfileHelper::handleImageUpload($data->image, $customer);
        }

        $customer->refresh();

        Event::dispatch('customer.update.after', $customer);

        // Get the mapped profile
        $profile = CustomerProfileHelper::mapCustomerToProfile($customer);

        // Add success fields for response
        $profile->success = true;
        $profile->message = __('bagistoapi::app.graphql.customer.profile-updated-successfully');

        // Create a CustomerProfileUpdate wrapper for the response
        $response = new \Webkul\BagistoApi\Models\CustomerProfileUpdate;
        // Copy profile data to response object
        foreach (get_object_vars($profile) as $key => $value) {
            if (property_exists($response, $key)) {
                $response->$key = $value;
            }
        }

        return $response;
    }

    /**
     * Handle customer profile deletion.
     */
    private function handleDelete(Customer $authenticatedCustomer): null
    {
        if ($authenticatedCustomer->image) {
            Storage::delete($authenticatedCustomer->image);
        }

        Event::dispatch('customer.delete.before', $authenticatedCustomer);

        // DB::table('personal_access_tokens')
        //     ->where('tokenable_id', $authenticatedCustomer->id)
        //     ->where('tokenable_type', Customer::class)
        //     ->delete();

        $authenticatedCustomer->delete();

        Event::dispatch('customer.delete.after', $authenticatedCustomer);

        return null;
    }
}
