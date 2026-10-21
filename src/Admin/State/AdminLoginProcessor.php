<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Validation\Rule;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminLogin;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthService;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAdminTokenPair;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;

class AdminLoginProcessor implements ProcessorInterface
{
    use MapsAdminTokenPair;
    use ReadsAdminAuthInput;

    /**
     * Create a new admin login processor.
     */
    public function __construct(protected AdminApiAuthService $authService) {}

    /**
     * Log an admin in with email and password, over REST or GraphQL, and return the token pair.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminLogin
    {
        $input = $this->validateAuthInput($this->authInput($operation, $context), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'client_type' => ['nullable', 'string', Rule::in(AdminApiSession::CLIENT_TYPES)],
        ]);

        $pair = $this->authService->login($input, request());

        return $this->mapTokenPair($pair, new AdminLogin);
    }
}
