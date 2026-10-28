<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use Webkul\BagistoApi\Dto\CustomerProfileOutput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Helper\CustomerProfileHelper;
use Webkul\BagistoApi\Models\CustomerProfile as CustomerProfileModel;
use Webkul\BagistoApi\State\Concerns\SyncsNewsletterSubscription;
use Webkul\BagistoApi\Validators\CustomerValidator;
use Webkul\Core\Repositories\SubscribersListRepository;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;

class CustomerProfileProcessor implements ProcessorInterface
{
    use SyncsNewsletterSubscription;

    public function __construct(
        protected CustomerValidator $validator,
        protected SubscribersListRepository $subscriptionRepository
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

        // REST fallback: the project-wide OutputOnlySnakeToCamelNameConverter converts
        // incoming JSON keys camelCase → snake_case during DTO denormalization, so
        // multi-word body fields like `currentPassword` / `confirmPassword` /
        // `dateOfBirth` / `subscribedToNewsLetter` / `firstName` etc never land on
        // the camelCase-named DTO properties. Read the raw body and merge it in so
        // every field is available to handleUpdate(). Skip this for GraphQL (the
        // merge above already populated $data from $context['args']['input']).
        if ($request && empty($context['args']['input'])) {
            $body = $request->json()->all() ?: $request->all();
            if (is_array($body) && ! empty($body)) {
                if (is_object($data)) {
                    $dataArray = array_filter(
                        (array) $data,
                        static fn ($v) => $v !== null
                    );
                    $data = (object) array_merge($body, $dataArray);
                    // Body wins for keys not yet set on the DTO.
                    foreach ($body as $k => $v) {
                        if ($v !== null && (! isset($data->{$k}) || $data->{$k} === null)) {
                            $data->{$k} = $v;
                        }
                    }
                } else {
                    $data = (object) $body;
                }
            }
        }

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
            return $this->handleDelete($data, $authenticatedCustomer);
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
     * The email column is unique per channel, so a clash would otherwise surface
     * as a raw database error instead of a validation failure.
     */
    /**
     * The storefront profile rules (ProfileRequest) for the fields being changed: email unique in
     * the channel, phone format and uniqueness, birth date in the past, new password of 6 or more.
     */
    private function validateProfileFields(mixed $data, Customer $customer): void
    {
        $fields = [
            'email' => ['email', Rule::unique('customers', 'email')->ignore($customer->id)->where('channel_id', $customer->channel_id)],
            'phone' => [new PhoneNumber, Rule::unique('customers', 'phone')->ignore($customer->id)],
            'dateOfBirth' => ['date', 'before:today'],
            'password' => ['min:6'],
        ];

        $values = array_filter(
            array_map(fn ($field) => is_object($data) ? ($data->{$field} ?? null) : null, array_combine(array_keys($fields), array_keys($fields))),
            fn ($value) => $value !== null && $value !== ''
        );

        $validator = Validator::make($values, array_intersect_key($fields, $values));

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
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

        $this->validateProfileFields($data, $authenticatedCustomer);

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
            // Validate and normalize gender
            $updateData['gender'] = $this->validator->validateGender($data->gender);
        }

        if (is_object($data) && property_exists($data, 'dateOfBirth') && ! empty($data->dateOfBirth)) {
            $updateData['date_of_birth'] = $data->dateOfBirth;
        }

        if (is_object($data) && property_exists($data, 'password') && ! empty($data->password)) {
            $currentPassword = property_exists($data, 'currentPassword') ? $data->currentPassword : null;

            if (empty($currentPassword)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.customer.current-password-required'));
            }

            if (! Hash::check($currentPassword, $authenticatedCustomer->password)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.customer.current-password-incorrect'));
            }

            if (empty($data->confirmPassword ?? null)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.customer.confirm-password-required'));
            }

            if ($data->password !== $data->confirmPassword) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.customer.password-mismatch'));
            }

            if (! Hash::isHashed($data->password)) {
                $updateData['password'] = Hash::make($data->password);
            }
        }

        if (is_object($data) && property_exists($data, 'subscribedToNewsLetter') && $data->subscribedToNewsLetter !== null) {
            $updateData['subscribed_to_news_letter'] = $data->subscribedToNewsLetter;
        }

        // Note: status, isVerified, isSuspended are admin-only fields.
        // Customers cannot change these via the profile update endpoint.

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

        if (isset($updateData['password'])) {
            Event::dispatch('customer.password.update.after', $authenticatedCustomer);
        }

        Event::dispatch('customer.update.after', $authenticatedCustomer);

        if (array_key_exists('subscribed_to_news_letter', $updateData)) {
            $this->syncSubscriptionOnProfileUpdate($authenticatedCustomer, (bool) $updateData['subscribed_to_news_letter']);
        }

        $output = CustomerProfileHelper::mapCustomerToProfileOutput($authenticatedCustomer);
        $output->success = true;
        $output->message = __('bagistoapi::app.graphql.customer-profile.profile-updated');

        return $output;

    }

    /**
     * Handle customer profile deletion.
     */
    /**
     * Mirror the storefront delete: the password must match, and an account with a pending
     * or processing order cannot be deleted.
     */
    private function handleDelete(mixed $data, Customer $authenticatedCustomer): null
    {
        $password = is_object($data) ? ($data->password ?? null) : null;

        if (empty($password)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.delete-password-required'), 422);
        }

        if (! Hash::check((string) $password, $authenticatedCustomer->password)) {
            throw new InvalidInputException(trans('shop::app.customers.account.profile.index.wrong-password'), 422);
        }

        if ($authenticatedCustomer->orders()->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])->exists()) {
            throw new InvalidInputException(trans('shop::app.customers.account.profile.index.order-pending'), 422);
        }

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

            $personalAccessToken = PersonalAccessToken::findToken($token);

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
            if (preg_match('/^data:image\/(bmp|jpe?g|png|webp);base64,/i', $imageData)) {
                $base64Data = substr($imageData, strpos($imageData, ',') + 1);
                $decodedData = base64_decode($base64Data, true);

                if ($decodedData === false) {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-base64'));
                }

                if (strlen($decodedData) > 5 * 1024 * 1024) {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.upload.size-exceeds-limit'));
                }

                $imageFormat = CustomerProfileHelper::verifiedProfileImageExtension($decodedData);

                if (! $imageFormat) {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.upload.invalid-format'));
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
