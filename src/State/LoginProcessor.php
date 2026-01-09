<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Dto\LoginInput;
use Webkul\BagistoApi\Validators\LoginValidator;
use Webkul\Customer\Models\Customer;

class LoginProcessor implements ProcessorInterface
{
    public function __construct(
        protected LoginValidator $validator
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof LoginInput) {
            if ($operation->getName() === 'create') {
                $this->validator->validateLoginInput($data);

                $customer = Customer::where('email', $data->email)->first();

                if (! $customer || ! Hash::check($data->password, $customer->password)) {
                    return (object) [
                        'id'       => 0,
                        'apiToken' => '',
                        'token'    => '',
                        'success'  => false,
                        'message'  => __('bagistoapi::app.graphql.login.invalid-credentials'),
                    ];
                }

                if ($customer->is_suspended) {
                    return (object) [
                        'id'       => 0,
                        'apiToken' => '',
                        'token'    => '',
                        'success'  => false,
                        'message'  => __('bagistoapi::app.graphql.login.account-suspended'),
                    ];
                }

                if (empty($customer->api_token)) {
                    $customer->api_token = Str::random(80);
                    $customer->save();
                }

                $token = $customer->createToken('customer-login')->plainTextToken;

                return (object) [
                    'id'       => $customer->id,
                    'apiToken' => $customer->api_token,
                    'token'    => $token,
                    'success'  => true,
                    'message'  => __('bagistoapi::app.graphql.login.successful'),
                ];
            }
        }

        return (object) [
            'id'       => 0,
            'apiToken' => '',
            'token'    => '',
            'success'  => false,
            'message'  => __('bagistoapi::app.graphql.login.invalid-request'),
        ];
    }
}
