<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Dto\CustomerProfileOutput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Helper\CustomerProfileHelper;
use Webkul\BagistoApi\Models\CustomerProfile as CustomerProfileModel;
use Webkul\BagistoApi\Validators\CustomerValidator;
use Webkul\Customer\Models\Customer;

class CustomerProfileProcessor implements ProcessorInterface
{
    public function __construct(
        protected CustomerValidator $validator
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // For GraphQL mutations, always prefer context args input as it's the source of truth
        // The denormalized object may not have all fields properly populated
        if (isset($context['args']['input']) && is_array($context['args']['input'])) {
            $inputData = $context['args']['input'];

            // Merge with existing data, preferring args values
            if (is_object($data)) {
                $dataArray = (array) $data;
                $data = (object) array_merge($dataArray, $inputData);
            } else {
                $data = (object) $inputData;
            }
        }

        $request = Request::instance() ?? ($context['request'] ?? null);

        if (! $request) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.request-not-found'));
        }

        $token = null;
        if (is_object($data) && property_exists($data, 'token')) {
            $token = $data->token;
        }
        if (! $token) {
            $token = $this->extractToken($request);
        }

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.token-required'));
        }

        $authenticatedCustomer = $this->getCustomerFromToken($token);

        if (! $authenticatedCustomer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.invalid-or-expired-token'));
        }

        $resourceClass = $operation->getClass();
        $resourceShortName = class_basename($resourceClass);

        if ($resourceShortName === 'CustomerProfileDelete') {
            return $this->handleDelete($authenticatedCustomer);
        } elseif ($resourceShortName === 'CustomerProfileUpdate') {
            return $this->handleUpdate($data, $authenticatedCustomer);
        } elseif ($resourceShortName === 'CustomerProfile') {
            return $this->mapCustomerToProfile($authenticatedCustomer);
        }

        throw new \InvalidArgumentException(__('bagistoapi::app.graphql.auth.unknown-resource'));
    }

    /**
     * Map customer model to DTO object
     */
    private function mapCustomerToProfile(Customer $authenticatedCustomer): CustomerProfileModel
    {
        return CustomerProfileHelper::mapCustomerToProfile($authenticatedCustomer);
    }

    /**
     * Handle customer profile update.
     */
    private function handleUpdate(mixed $data, Customer $authenticatedCustomer): CustomerProfileOutput
    {
        $updateData = [];

        if (is_object($data) && property_exists($data, 'id') && $data->id) {
            if ((int) $data->id !== (int) $authenticatedCustomer->id) {
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
            // Validate phone - no special characters allowed
            $this->validatePhone($data->phone);
            $updateData['phone'] = $data->phone;
        }

        if (is_object($data) && property_exists($data, 'gender') && ! empty($data->gender)) {
            // Validate and normalize gender
            $updateData['gender'] = $this->validator->validateGender($data->gender);
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

        if (is_object($data) && property_exists($data, 'status') && ! empty($data->status)) {
            $updateData['status'] = $data->status;
        }

        if (is_object($data) && property_exists($data, 'isVerified') && ! empty($data->isVerified)) {
            $updateData['is_verified'] = $data->isVerified;
        }

        if (is_object($data) && property_exists($data, 'isSuspended') && ! empty($data->isSuspended)) {
            $updateData['is_suspended'] = $data->isSuspended;
        }

        Event::dispatch('customer.update.before');

        if (! empty($updateData)) {
            $authenticatedCustomer->update($updateData);
        }

        if (is_object($data) && property_exists($data, 'deleteImage') && $data->deleteImage) {
            if ($authenticatedCustomer->image) {
                Storage::delete($authenticatedCustomer->image);
                $authenticatedCustomer->update(['image' => null]);
            }
        } elseif (is_object($data) && property_exists($data, 'image') && ! empty($data->image)) {
            $this->handleImageUpload($data->image, $authenticatedCustomer);
        }

        $authenticatedCustomer->refresh();

        Event::dispatch('customer.update.after', $authenticatedCustomer);

        $output = CustomerProfileHelper::mapCustomerToProfileOutput($authenticatedCustomer);
        $output->success = true;
        $output->message = __('bagistoapi::app.graphql.customer-profile.profile-updated');

        return $output;

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

        DB::table('personal_access_tokens')
            ->where('tokenable_id', $authenticatedCustomer->id)
            ->where('tokenable_type', Customer::class)
            ->delete();

        $authenticatedCustomer->delete();

        Event::dispatch('customer.delete.after', $authenticatedCustomer);

        return null;
    }

    /**
     * Extract token from Authorization header or input parameter.
     */
    private function extractToken($request): ?string
    {
        $authHeader = $request->header('Authorization');

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        return $request->input('token');
    }

    /**
     * Get customer from Sanctum token.
     */
    private function getCustomerFromToken(string $token): ?Customer
    {
        try {
            if (strpos($token, '|') === false) {
                return null;
            }

            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

            if (! $personalAccessToken) {
                return null;
            }

            if (! $personalAccessToken->tokenable instanceof Customer) {
                return null;
            }

            return $personalAccessToken->tokenable;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Handle image upload with base64 encoding.
     */
    private function handleImageUpload(string $imageData, Customer $customer): void
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                $imageFormat = $matches[1];
                $base64Data = substr($imageData, strpos($imageData, ',') + 1);
                $decodedData = base64_decode($base64Data, true);

                if ($decodedData === false) {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-base64'));
                }

                if (strlen($decodedData) > 5 * 1024 * 1024) {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.upload.size-exceeds-limit'));
                }

                $directory = 'customer/'.$customer->id;

                if ($customer->image) {
                    Storage::delete($customer->image);
                }

                $filename = $directory.'/'.uniqid().'.'.$imageFormat;

                Storage::put($filename, $decodedData);

                $customer->image = $filename;
                $customer->save();

                Event::dispatch('customer.image.upload.after', $customer);
            } else {
                throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-format'));
            }
        } catch (\Exception $e) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.failed'));
        }
    }

    /**
     * Validate phone number - only digits allowed
     *
     * @throws InvalidInputException
     */
    private function validatePhone(?string $phone): void
    {
        if ($phone === null || $phone === '') {
            return;
        }

        // Phone should only contain digits - remove all non-digit characters
        $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);

        // If the cleaned phone is different from original, it means special characters were present
        if ($cleanedPhone !== $phone) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.phone-special-chars-not-allowed'));
        }
    }
}
