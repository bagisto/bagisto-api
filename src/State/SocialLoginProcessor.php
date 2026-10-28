<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Dto\SocialLoginInput;
use Webkul\BagistoApi\Exception\SocialLoginException;
use Webkul\BagistoApi\Models\SocialLogin;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthService;
use Webkul\BagistoApi\Services\SocialLoginService;
use Webkul\BagistoApi\Services\SocialLoginTokenVerifier;
use Webkul\BagistoApi\State\Concerns\MapsCustomerTokenPair;

class SocialLoginProcessor implements ProcessorInterface
{
    use MapsCustomerTokenPair;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected SocialLoginTokenVerifier $verifier,
        protected SocialLoginService $socialLogin,
        protected CustomerApiAuthService $authService,
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): SocialLogin
    {
        try {
            [$provider, $idToken, $accessToken, $deviceToken] = $this->resolveInput($data, $context);

            $driver = $this->verifier->driverFor((string) $provider);

            $profile = $this->verifier->verify((string) $provider, $driver, $idToken, $accessToken);

            [
                'customer' => $customer,
                'isNew' => $isNew,
            ] = $this->socialLogin->resolve($profile, $driver);

            if (! $customer->status) {
                throw new SocialLoginException(
                    'ACCOUNT_INACTIVE',
                    trans('bagistoapi::app.graphql.social-login.account-inactive'),
                );
            }

            return $this->success($customer, $isNew, $deviceToken);
        } catch (SocialLoginException $exception) {
            return $this->failure($exception->errorCode, $exception->getMessage());
        }
    }

    /**
     * Resolve the provider, id token, access token and device token from the request.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string, 3: ?string}
     */
    protected function resolveInput(mixed $data, array $context): array
    {
        $input = $context['args']['input'] ?? [];

        $dto = $data instanceof SocialLoginInput ? $data : null;

        $pick = fn (array $candidates): ?string => $this->firstFilled($candidates);

        return [
            $pick([$dto?->provider, $input['provider'] ?? null, request()->input('provider')]),
            $pick([$dto?->idToken, $input['idToken'] ?? null, $input['id_token'] ?? null, request()->input('idToken'), request()->input('id_token')]),
            $pick([$dto?->accessToken, $input['accessToken'] ?? null, $input['access_token'] ?? null, request()->input('accessToken'), request()->input('access_token')]),
            $pick([$dto?->deviceToken, $input['deviceToken'] ?? null, $input['device_token'] ?? null, request()->input('deviceToken'), request()->input('device_token')]),
        ];
    }

    /**
     * First filled.
     */
    protected function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            if (
                is_string($value)
                && trim($value) !== ''
            ) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Success.
     */
    protected function success(mixed $customer, bool $isNew, ?string $deviceToken): SocialLogin
    {
        if (empty($customer->api_token)) {
            $customer->api_token = Str::random(80);

            $customer->save();
        }

        if ($deviceToken) {
            Event::dispatch('bagistoapi.customer.device-token.save', [
                'customerId' => $customer->id,
                'deviceToken' => $deviceToken,
            ]);
        }

        $pair = $this->authService->startSession($customer, request());

        $output = $this->mapTokenPair($pair, new SocialLogin);

        $output->id = (int) $customer->id;
        $output->_id = (int) $customer->id;
        $output->token = $pair->accessToken;
        $output->api_token = $customer->api_token;
        $output->first_name = $customer->first_name;
        $output->last_name = $customer->last_name;
        $output->email = $customer->email;
        $output->phone = $customer->phone;
        $output->is_new_customer = $isNew;
        $output->success = true;
        $output->message = trans('bagistoapi::app.graphql.social-login.signed-in');
        $output->code = null;

        return $output;
    }

    /**
     * Failure.
     */
    protected function failure(string $code, string $message): SocialLogin
    {
        $output = new SocialLogin;

        $output->success = false;
        $output->message = $message;
        $output->code = $code;

        return $output;
    }
}
