<?php

namespace Webkul\BagistoApi\Validators;

use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\LoginInput;
use Webkul\BagistoApi\Exception\InvalidInputException;

class LoginValidator
{
    public function validateLoginInput(LoginInput $login): void
    {
        $data = [
            'email'    => $login->email,
            'password' => $login->password,
        ];

        $rules = [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            throw new InvalidInputException($errorMessage);
        }
    }
}
