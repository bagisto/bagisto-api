<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Operation as GraphQlOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminLogin;
use Webkul\BagistoApi\Admin\Services\AdminApiAuthService;
use Webkul\BagistoApi\Admin\Services\AdminApiTwoFactorChallengeToken;
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
     * Log an admin in with email and password, over REST or GraphQL, and return the token pair or a challenge.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminLogin
    {
        $input = $this->validateAuthInput($this->authInput($operation, $context), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'client_type' => ['nullable', 'string', Rule::in(AdminApiSession::CLIENT_TYPES)],
        ]);

        $result = $this->authService->login($input, request());

        if ($result instanceof AdminApiTwoFactorChallengeToken) {
            return $this->mapChallenge($result, $operation, $context);
        }

        $login = $this->mapTokenPair($result, new AdminLogin);

        $login->two_factor_required = false;

        return $login;
    }

    /**
     * The 202 answer of a login that needs a second factor: the challenge token and no other token.
     */
    protected function mapChallenge(AdminApiTwoFactorChallengeToken $challenge, Operation $operation, array $context): AdminLogin
    {
        if (! $operation instanceof GraphQlOperation) {
            ($context['request'] ?? request())->attributes->set('_api_response_status', Response::HTTP_ACCEPTED);
        }

        $login = new AdminLogin;

        $login->two_factor_required = true;
        $login->challenge_token = $challenge->token;
        $login->challenge_token_expires_at = $challenge->challenge->expires_at->toIso8601String();
        $login->message = trans('bagistoapi::app.admin.auth.errors.two-factor-required');

        return $login;
    }
}
