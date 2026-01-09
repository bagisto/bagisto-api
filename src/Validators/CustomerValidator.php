<?php

namespace Webkul\BagistoApi\Validators;

use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Customer\Models\Customer;

class CustomerValidator
{
    /**
     * Validate customer for creation
     *
     * @throws InvalidInputException
     */
    public function validateForCreation(Customer $customer): void
    {
        $rules = [
            'first_name' => 'string|required',
            'last_name'  => 'string|required',
            'email'      => 'email|required|unique:customers,email',
            'phone'      => 'string|nullable|unique:customers,phone',
            'password'   => 'min:6|required',
        ];

        $data = [
            'first_name'            => $customer->first_name,
            'last_name'             => $customer->last_name,
            'email'                 => $customer->email,
            'phone'                 => $customer->phone,
            'password'              => $customer->password,
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);

            throw new InvalidInputException($errorMessage);
        }
    }

    /**
     * Validate customer for update
     * Only validates fields that have been changed (non-null values)
     *
     * @throws InvalidInputException
     */
    public function validateForUpdate(Customer $customer): void
    {
        $data = [];
        $rules = [];

        // Only include and validate fields that have been set
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
            $rules['email'] = 'email|unique:customers,email,'.$customer->id;
        }

        if ($customer->phone !== null) {
            $data['phone'] = $customer->phone;
            $rules['phone'] = 'string|unique:customers,phone,'.$customer->id;
        }

        // Only validate password if it's actually being changed
        if (! empty($customer->password) && ! empty($customer->confirm_password)) {
            $data['password'] = $customer->password;
            $data['password_confirmation'] = $customer->confirm_password;
            $rules['password'] = 'confirmed|min:6';
        }

        // Only validate if there are rules to check
        if (! empty($rules)) {
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $errorMessage = implode(' ', $errors);
                throw new InvalidInputException($errorMessage);
            }
        }
    }
}
