<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;
use Webkul\BagistoApi\Models\CustomerTokenRefresh;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthService;
use Webkul\BagistoApi\State\Concerns\MapsCustomerTokenPair;

class CustomerTokenRefreshProcessor implements ProcessorInterface
{
    use MapsCustomerTokenPair;
    use ReadsAdminAuthInput;

    /**
     * Create a new customer token refresh processor.
     */
    public function __construct(protected CustomerApiAuthService $authService) {}

    /**
     * Exchange a customer refresh token for a new token pair, over REST or GraphQL.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CustomerTokenRefresh
    {
        $input = $this->validateAuthInput($this->authInput($operation, $context), [
            'refresh_token' => ['required', 'string', 'max:4096'],
        ]);

        $pair = $this->authService->refresh($input['refresh_token'], request());

        return $this->mapTokenPair($pair, new CustomerTokenRefresh);
    }
}
