<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Dto\LoginInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\CustomerLogin;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthService;
use Webkul\BagistoApi\State\Concerns\MapsCustomerTokenPair;
use Webkul\BagistoApi\Validators\LoginValidator;

class LoginProcessor implements ProcessorInterface
{
    use MapsCustomerTokenPair;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected LoginValidator $validator,
        protected CustomerApiAuthService $authService,
    ) {}

    /**
     * Check the customer's credentials on the current channel and return an access and refresh token pair.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CustomerLogin
    {
        if (! $data instanceof LoginInput) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.login.invalid-request'));
        }

        $this->validator->validateLoginInput($data);

        $input = $context['args']['input'] ?? [];

        $pair = $this->authService->login($data->email, $data->password, request(), [
            'name' => $this->pick([$data->deviceName, $input['deviceName'] ?? null, request()->input('device_name'), request()->input('deviceName')]),
            'client_type' => $this->pick([$data->clientType, $input['clientType'] ?? null, request()->input('client_type'), request()->input('clientType')]),
        ], $this->pick([$data->guestCartToken, $input['guestCartToken'] ?? null, request()->input('guest_cart_token'), request()->input('guestCartToken')]));

        $customer = $pair->session->customer;

        if (empty($customer->api_token)) {
            $customer->api_token = Str::random(80);
            $customer->save();
        }

        $deviceToken = $this->pick([$data->deviceToken, $input['deviceToken'] ?? null, request()->input('device_token'), request()->input('deviceToken')]);

        if ($deviceToken) {
            Event::dispatch('bagistoapi.customer.device-token.save', [
                'customerId' => $customer->id,
                'deviceToken' => $deviceToken,
            ]);
        }

        $output = $this->mapTokenPair($pair, new CustomerLogin);

        $output->id = (int) $customer->id;
        $output->_id = (int) $customer->id;
        $output->api_token = $customer->api_token;
        $output->token = $pair->accessToken;
        $output->cart_id = $pair->mergedCartId;
        $output->success = true;
        $output->message = __('bagistoapi::app.graphql.login.successful');

        return $output;
    }

    /**
     * The first non-empty string among the candidates.
     */
    protected function pick(array $candidates): ?string
    {
        foreach ($candidates as $value) {
            if (
                is_string($value)
                && trim($value) !== ''
            ) {
                return $value;
            }
        }

        return null;
    }
}
