<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\CustomerProfileOutput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Helper\CustomerProfileHelper;
use Webkul\BagistoApi\Models\CustomerProfile as CustomerProfileModel;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\BagistoApi\Validators\CustomerValidator;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;

class CustomerProfileProcessor implements ProcessorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected CustomerValidator $validator
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $data = $this->mergeGraphQlInput($data, $context);

        $request = Request::instance() ?? ($context['request'] ?? null);

        if (
            $request
            && empty($context['args']['input'])
        ) {
            $data = $this->mergeRestBody($data, $request);
        }

        if (! $request) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.request-not-found'));
        }

        $authenticatedCustomer = $this->authenticate($data, $request);

        return match (class_basename($operation->getClass())) {
            'CustomerProfileDelete' => $this->handleDelete($authenticatedCustomer, $data),
            'CustomerProfileUpdate' => $this->handleUpdate($data, $authenticatedCustomer),
            'CustomerProfile' => $this->mapCustomerToProfile($authenticatedCustomer),
            default => throw new \InvalidArgumentException(__('bagistoapi::app.graphql.auth.unknown-resource')),
        };
    }

    /**
     * The GraphQL mutation input is the source of truth; the denormalized object may be incomplete.
     */
    private function mergeGraphQlInput(mixed $data, array $context): mixed
    {
        $inputData = $context['args']['input'] ?? null;

        if (! is_array($inputData)) {
            return $data;
        }

        return is_object($data)
            ? (object) array_merge((array) $data, $inputData)
            : (object) $inputData;
    }

    /**
     * The name converter turns camelCase JSON keys into snake_case, so multi-word body fields never
     * reach the camelCase DTO properties. Read the raw body and let set DTO values win over it.
     */
    private function mergeRestBody(mixed $data, mixed $request): mixed
    {
        $body = $request->json()->all() ?: $request->all();

        if (
            ! is_array($body)
            || empty($body)
        ) {
            return $data;
        }

        if (! is_object($data)) {
            return (object) $body;
        }

        return (object) array_merge($body, array_filter((array) $data, static fn ($value) => $value !== null));
    }

    /**
     * Resolve the customer that owns the bearer token.
     */
    private function authenticate(mixed $data, mixed $request): Customer
    {
        $token = is_object($data) && property_exists($data, 'token') ? $data->token : null;
        $token = $token ?: $this->extractToken($request);

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.token-required'));
        }

        $authenticatedCustomer = $this->getCustomerFromToken($token);

        if (! $authenticatedCustomer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.auth.invalid-or-expired-token'));
        }

        return $authenticatedCustomer;
    }

    /**
     * Map customer model to DTO object.
     */
    private function mapCustomerToProfile(Customer $authenticatedCustomer): CustomerProfileModel
    {
        return CustomerProfileHelper::mapCustomerToProfile($authenticatedCustomer);
    }

    /**
     * Refuse an invalid email, and one another customer of the same channel uses, as core's profile request does.
     */
    private function assertEmailAvailable(string $email, Customer $authenticatedCustomer): void
    {
        $validator = Validator::make(['email' => $email], ['email' => 'email']);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first('email'));
        }

        $taken = Customer::where('email', $email)
            ->where('channel_id', $authenticatedCustomer->channel_id)
            ->where('id', '!=', $authenticatedCustomer->id)
            ->exists();

        if ($taken) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.email-already-taken'));
        }
    }

    /**
     * Handle customer profile update.
     */
    private function handleUpdate(mixed $data, Customer $authenticatedCustomer): CustomerProfileOutput
    {
        $updateData = [];

        if (
            is_object($data)
            && property_exists($data, 'id')
            && $data->id
        ) {
            if ((int) $data->id !== (int) $authenticatedCustomer->id) {
                throw new AuthenticationException(__('bagistoapi::app.graphql.auth.cannot-update-other-profile'));
            }
        }

        if (
            is_object($data)
            && property_exists($data, 'firstName')
            && ! empty($data->firstName)
        ) {
            $updateData['first_name'] = $data->firstName;
        }

        if (
            is_object($data)
            && property_exists($data, 'lastName')
            && ! empty($data->lastName)
        ) {
            $updateData['last_name'] = $data->lastName;
        }

        if (
            is_object($data)
            && property_exists($data, 'email')
            && ! empty($data->email)
        ) {
            $this->assertEmailAvailable($data->email, $authenticatedCustomer);

            $updateData['email'] = $data->email;
        }

        if (
            is_object($data)
            && property_exists($data, 'phone')
            && ! empty($data->phone)
        ) {
            $this->validatePhone($data->phone);
            $updateData['phone'] = $data->phone;
        }

        if (
            is_object($data)
            && property_exists($data, 'gender')
            && ! empty($data->gender)
        ) {
            $updateData['gender'] = $this->validator->validateGender($data->gender);
        }

        if (
            is_object($data)
            && property_exists($data, 'dateOfBirth')
            && ! empty($data->dateOfBirth)
        ) {
            $updateData['date_of_birth'] = $data->dateOfBirth;
        }

        if (
            is_object($data)
            && property_exists($data, 'password')
            && ! empty($data->password)
        ) {
            $currentPassword = property_exists($data, 'currentPassword') ? $data->currentPassword : null;

            if (empty($currentPassword)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.customer.current-password-required'));
            }

            if (! Hash::check($currentPassword, $authenticatedCustomer->password)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.customer.current-password-incorrect'));
            }

            if (
                is_object($data)
                && property_exists($data, 'confirmPassword')
            ) {
                if ($data->password !== $data->confirmPassword) {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.customer.password-mismatch'));
                }
            }

            if (! Hash::isHashed($data->password)) {
                $updateData['password'] = Hash::make($data->password);
            }
        }

        if (
            is_object($data)
            && property_exists($data, 'subscribedToNewsLetter')
            && $data->subscribedToNewsLetter !== null
        ) {
            $updateData['subscribed_to_news_letter'] = filter_var($data->subscribedToNewsLetter, FILTER_VALIDATE_BOOLEAN);
        }

        Event::dispatch('customer.update.before');

        if (! empty($updateData)) {
            $authenticatedCustomer->update($updateData);
        }

        if (
            is_object($data)
            && property_exists($data, 'deleteImage')
            && $data->deleteImage
        ) {
            if ($authenticatedCustomer->image) {
                Storage::delete($authenticatedCustomer->image);
                $authenticatedCustomer->update(['image' => null]);
            }
        } elseif (
            is_object($data)
            && property_exists($data, 'image')
            && ! empty($data->image)
        ) {
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
    private function handleDelete(Customer $authenticatedCustomer, mixed $data): null
    {
        $password = is_object($data) ? ($data->password ?? null) : null;

        if (empty($password)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.delete-password-required'), 422);
        }

        if (! Hash::check($password, $authenticatedCustomer->password)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.delete-password-incorrect'), 422);
        }

        $hasActiveOrders = $authenticatedCustomer->orders()
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
            ->exists();

        if ($hasActiveOrders) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.delete-has-active-orders'), 422);
        }

        if ($authenticatedCustomer->image) {
            Storage::delete($authenticatedCustomer->image);
        }

        Event::dispatch('customer.delete.before', $authenticatedCustomer);

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

        if (
            $authHeader
            && str_starts_with($authHeader, 'Bearer ')
        ) {
            return substr($authHeader, 7);
        }

        return $request->input('token');
    }

    /**
     * Resolve the customer behind a customer access token.
     */
    private function getCustomerFromToken(string $token): ?Customer
    {
        return CurrentCustomer::fromToken($token);
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
        } catch (\Exception $exception) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.upload.failed'));
        }
    }

    /**
     * Validate phone number - only digits allowed.
     *
     * @throws InvalidInputException
     */
    private function validatePhone(?string $phone): void
    {
        if (
            $phone === null
            || $phone === ''
        ) {
            return;
        }

        $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);

        if ($cleanedPhone !== $phone) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.customer.phone-special-chars-not-allowed'));
        }
    }
}
