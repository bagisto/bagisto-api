<?php

namespace Webkul\BagistoApi\Admin\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;

class AdminApiGuard implements Guard
{
    use GuardHelpers;

    protected Request $request;

    protected ?AdminPersonalAccessToken $currentToken = null;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user(): ?Authenticatable
    {
        $request = app('request') ?: $this->request;

        $bearer = $request->bearerToken();

        if ($this->user !== null
            && $this->currentToken !== null
            && $bearer !== null
            && str_contains($bearer, '|')
        ) {
            [$id] = explode('|', $bearer, 2);

            if (ctype_digit($id) && (int) $id === (int) $this->currentToken->id) {
                return $this->user;
            }
        }

        $this->user = null;
        $this->currentToken = null;

        if (! $bearer) {
            return null;
        }

        if (! str_contains($bearer, '|')) {
            return null;
        }

        [$id, $plain] = explode('|', $bearer, 2);

        if (! ctype_digit($id) || $plain === '') {
            return null;
        }

        $token = AdminPersonalAccessToken::find((int) $id);

        if (! $token || ! $token->isUsable()) {
            return null;
        }

        if (! hash_equals((string) $token->token, hash('sha256', $plain))) {
            return null;
        }

        $clientIp = $this->request->ip() ?? '';

        if (! $token->isIpAllowed($clientIp)) {
            Log::warning('Admin API token denied by IP allowlist', [
                'token_id' => $token->id,
                'admin_id' => $token->admin_id,
                'client_ip' => $clientIp,
            ]);

            return null;
        }

        $admin = $this->provider->retrieveById($token->admin_id);

        if (! $admin) {
            return null;
        }

        $token->forceFill(['last_used_at' => now()])->saveQuietly();

        $this->currentToken = $token;

        if (method_exists($admin, 'withAccessToken')) {
            $admin->withAccessToken($token);
        } else {
            $admin->setAttribute('current_access_token', $token);
        }

        // Constrain the resolved admin's effective role to the token's abilities so
        // every endpoint permission check honours the token's permission_type
        // (custom tokens are restricted to their frozen abilities).
        $token->applyAbilityScope($admin);

        return $this->user = $admin;
    }

    public function currentAccessToken(): ?AdminPersonalAccessToken
    {
        $this->user();

        return $this->currentToken;
    }

    public function validate(array $credentials = []): bool
    {
        if (empty($credentials['token'])) {
            return false;
        }

        $request = Request::create('/', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$credentials['token']);

        $previousRequest = $this->request;
        $this->request = $request;

        try {
            return $this->user() !== null;
        } finally {
            $this->request = $previousRequest;
        }
    }
}
