<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminTwoFactorVerify;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthService;
use Webkul\BagistoApi\Admin\State\Concerns\MapsAdminTokenPair;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;

class AdminTwoFactorVerifyProcessor implements ProcessorInterface
{
    use MapsAdminTokenPair;
    use ReadsAdminAuthInput;

    /**
     * Create a new two-factor verify processor.
     */
    public function __construct(protected AdminApiAuthService $authService) {}

    /**
     * Exchange a challenge token and a code or backup code for a token pair, over REST or GraphQL.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminTwoFactorVerify
    {
        $input = $this->validateAuthInput($this->authInput($operation, $context), [
            'challenge_token' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'required_without:backup_code', 'prohibits:backup_code', 'digits:6'],
            'backup_code' => ['nullable', 'required_without:code', 'string', 'max:50'],
        ]);

        $pair = $this->authService->verifyTwoFactor($input, request());

        return $this->mapTokenPair($pair, new AdminTwoFactorVerify);
    }
}
