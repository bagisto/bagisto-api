<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminTokenRefresh;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthService;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAdminTokenPair;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;

class AdminTokenRefreshProcessor implements ProcessorInterface
{
    use MapsAdminTokenPair;
    use ReadsAdminAuthInput;

    /**
     * Create a new token refresh processor.
     */
    public function __construct(protected AdminApiAuthService $authService) {}

    /**
     * Exchange a refresh token for a new token pair, over REST or GraphQL.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminTokenRefresh
    {
        $input = $this->validateAuthInput($this->authInput($operation, $context), [
            'refresh_token' => ['required', 'string', 'max:255'],
        ]);

        $pair = $this->authService->refresh($input['refresh_token'], request());

        return $this->mapTokenPair($pair, new AdminTokenRefresh);
    }
}
