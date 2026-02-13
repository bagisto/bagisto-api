<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Webkul\Customer\Models\Customer;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Validators\CustomerValidator;

class CustomerProfileProcessor implements ProcessorInterface
{
    public function __construct(
        protected CustomerValidator $validator
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?array
    {
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
     * Map customer data to profile array
     */
    private function mapCustomerToProfile(Customer $authenticatedCustomer): array
    {
        $imageUrl = null;
        if ($authenticatedCustomer->image) {
            $imageUrl = Storage::url($authenticatedCustomer->image);
        }

        return [
            'id'                     => (string) $authenticatedCustomer->id,
            'firstName'              => $authenticatedCustomer->first_name,
            'lastName'               => $authenticatedCustomer->last_name,
            'email'                  => $authenticatedCustomer->email,
            'phone'                  => $authenticatedCustomer->phone,
            'gender'                 => $authenticatedCustomer->gender,
            'dateOfBirth'            => $authenticatedCustomer->date_of_birth,
            'status'                 => $authenticatedCustomer->status,
            'subscribedToNewsLetter' => $authenticatedCustomer->subscribed_to_news_letter,
            'isVerified'             => (string) $authenticatedCustomer->is_verified,
            'isSuspended'            => (string) $authenticatedCustomer->is_suspended,
            'image'                  => $imageUrl,
        ];
    }

    /**
     * Handle customer profile update.
     */
    private function handleUpdate(mixed $data, Customer $authenticatedCustomer): array
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

        return [
            'id'                     => (string) $authenticatedCustomer->id,
            'firstName'              => $authenticatedCustomer->first_name,
            'lastName'               => $authenticatedCustomer->last_name,
            'email'                  => $authenticatedCustomer->email,
            'phone'                  => $authenticatedCustomer->phone,
            'gender'                 => $authenticatedCustomer->gender,
            'dateOfBirth'            => $authenticatedCustomer->date_of_birth,
            'status'                 => $authenticatedCustomer->status,
            'subscribedToNewsLetter' => $authenticatedCustomer->subscribed_to_news_letter,
            'isVerified'             => (string) $authenticatedCustomer->is_verified,
            'isSuspended'            => (string) $authenticatedCustomer->is_suspended,
            'image'                  => $authenticatedCustomer->image ? Storage::url($authenticatedCustomer->image) : null,
        ];
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
            $tokenParts = explode('|', $token);

            if (count($tokenParts) !== 2) {
                return null;
            }

            $tokenId = $tokenParts[0];

            $personalAccessToken = DB::table('personal_access_tokens')
                ->where('id', $tokenId)
                ->where('tokenable_type', Customer::class)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();

            if (! $personalAccessToken) {
                return null;
            }

            return Customer::find($personalAccessToken->tokenable_id);
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
}
