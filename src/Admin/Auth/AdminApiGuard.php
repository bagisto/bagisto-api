<?php

namespace Webkul\BagistoApi\Admin\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
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
        if ($this->user !== null) {
            return $this->user;
        }

        $bearer = $this->request->bearerToken();

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
