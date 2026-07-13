<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Dto\LoginInput;
use Webkul\BagistoApi\Models\CustomerLogin;
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
            if ($operation->getName() === 'create' || $operation instanceof Post) {
                $this->validator->validateLoginInput($data);

                $customer = Customer::where('email', $data->email)->first();

                if (! $customer || ! Hash::check($data->password, $customer->password)) {
                    return $this->output([
                        'id' => 0,
                        '_id' => 0,
                        'apiToken' => '',
                        'token' => '',
                        'success' => false,
                        'message' => __('bagistoapi::app.graphql.login.invalid-credentials'),
                    ]);
                }

                if ($customer->is_suspended) {
                    return $this->output([
                        'id' => 0,
                        '_id' => 0,
                        'apiToken' => '',
                        'token' => '',
                        'success' => false,
                        'message' => __('bagistoapi::app.graphql.login.account-suspended'),
                    ]);
                }

                if (empty($customer->api_token)) {
                    $customer->api_token = Str::random(80);
                    $customer->save();
                }

                // Dispatch event to save device_token - PushNotification package will handle this
                $deviceToken = $data->deviceToken ?? null;
                if ($deviceToken) {
                    Event::dispatch('bagistoapi.customer.device-token.save', [
                        'customerId' => $customer->id,
                        'deviceToken' => $deviceToken,
                    ]);
                }

                $token = $customer->createToken('customer-login')->plainTextToken;

                return $this->output([
                    'id' => $customer->id,
                    '_id' => $customer->id,
                    'apiToken' => $customer->api_token,
                    'token' => $token,
                    'success' => true,
                    'message' => __('bagistoapi::app.graphql.login.successful'),
                ]);
            }
        }

        return $this->output([
            'id' => 0,
            '_id' => 0,
            'apiToken' => '',
            'token' => '',
            'success' => false,
            'message' => __('bagistoapi::app.graphql.login.invalid-request'),
        ]);
    }

    private function output(array $data): CustomerLogin
    {
        $output = new CustomerLogin;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }
}
