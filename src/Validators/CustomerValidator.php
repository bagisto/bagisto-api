<?php

namespace Webkul\BagistoApi\Validators;

use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Customer\Models\Customer;

class CustomerValidator
{
    /**
     * Valid gender values.
     */
    private const VALID_GENDERS = ['Male', 'Female', 'Other'];

    /**
     * Validate customer for creation.
     *
     * @throws InvalidInputException
     */
    public function validateForCreation(Customer $customer): void
    {
        $rules = [
            'first_name' => 'string|required',
            'last_name' => 'string|required',
            'email' => 'email|required|unique:customers,email,NULL,id,channel_id,'.$this->channelId($customer),
            'phone' => 'string|nullable|unique:customers,phone',
            'password' => 'min:6|required',
        ];

        $data = [
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'password' => $customer->password,
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);

            throw new InvalidInputException($errorMessage);
        }

        $this->validatePhone($customer->phone);
        $this->validateGender($customer->gender);
    }

    /**
     * Validate customer for update
     * Only validates fields that have been changed (non-null values).
     *
     * @throws InvalidInputException
     */
    public function validateForUpdate(Customer $customer): void
    {
        $data = [];
        $rules = [];

        if ($customer->first_name !== null) {
            $data['first_name'] = $customer->first_name;
            $rules['first_name'] = 'string';
        }

        if ($customer->last_name !== null) {
            $data['last_name'] = $customer->last_name;
            $rules['last_name'] = 'string';
        }

        if ($customer->email !== null) {
            $data['email'] = $customer->email;
            $rules['email'] = 'email|unique:customers,email,'.$customer->id.',id,channel_id,'.$this->channelId($customer);
        }

        if ($customer->phone !== null) {
            $data['phone'] = $customer->phone;
            $rules['phone'] = 'string|unique:customers,phone,'.$customer->id;
            $this->validatePhone($customer->phone);
        }

        if ($customer->gender !== null) {
            $this->validateGender($customer->gender);
        }

        if (
            ! empty($customer->password)
            && ! empty($customer->confirm_password)
        ) {
            $data['password'] = $customer->password;
            $data['password_confirmation'] = $customer->confirm_password;
            $rules['password'] = 'confirmed|min:6';
        }

        if (! empty($rules)) {
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $errorMessage = implode(' ', $errors);
                throw new InvalidInputException($errorMessage);
            }
        }
    }

    /**
     * Validate and normalize gender value
     * Only allows Male, Female, or Other (case-insensitive).
     *
     * @throws InvalidInputException
     */
    public function validateGender(?string $gender): ?string
    {
        if (
            $gender === null
            || $gender === ''
        ) {
            return null;
        }

        $normalized = ucfirst(trim($gender));

        if (! in_array($normalized, self::VALID_GENDERS, true)) {
            throw new InvalidInputException(
                __('bagistoapi::app.graphql.customer.invalid-gender', [
                    'gender' => $gender,
                    'valid' => implode(', ', self::VALID_GENDERS),
                ])
            );
        }

        return $normalized;
    }

    /**
     * The channel a customer belongs to.
     * The same address may register on each channel of a store, so an email is unique per.
     */
    protected function channelId(Customer $customer): int
    {
        return (int) ($customer->channel_id ?: core()->getCurrentChannel()->id);
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
