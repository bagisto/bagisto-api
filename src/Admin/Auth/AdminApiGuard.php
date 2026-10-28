<?php

namespace Webkul\BagistoApi\Admin\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Webkul\BagistoApi\Exception\AdminAuthException;

class AdminApiGuard implements Guard
{
    use GuardHelpers;

    /**
     * The request the guard was created for.
     */
    protected Request $request;

    /**
     * The request the cached result belongs to.
     */
    protected ?Request $resolvedRequest = null;

    /**
     * The bearer token the cached result belongs to.
     */
    protected ?string $resolvedBearer = null;

    /**
     * The session or API token the current request authenticated with.
     */
    protected ?AdminApiCredential $credential = null;

    /**
     * Why the current request's bearer token was rejected.
     */
    protected ?AdminAuthException $failure = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        protected AdminApiAuthenticator $authenticator,
    ) {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * The admin behind the request's access token or API token, resolved once per request.
     */
    public function user(): ?Authenticatable
    {
        $request = $this->currentRequest();

        $bearer = $request->bearerToken();

        if (
            $this->resolvedRequest === $request
            && $this->resolvedBearer === $bearer
        ) {
            return $this->user;
        }

        $this->forgetResolution();

        $this->resolvedRequest = $request;
        $this->resolvedBearer = $bearer;

        if (
            $bearer === null
            || $bearer === ''
        ) {
            return null;
        }

        try {
            $this->credential = $this->authenticator->authenticate($bearer, $request);
        } catch (AdminAuthException $exception) {
            $this->failure = $exception;

            return null;
        }

        return $this->user = $this->credential->admin;
    }

    /**
     * The session or API token the current request authenticated with.
     */
    public function credential(): ?AdminApiCredential
    {
        $this->user();

        return $this->credential;
    }

    /**
     * Why the current request's bearer token was rejected, or null when it was accepted or absent.
     */
    public function failure(): ?AdminAuthException
    {
        $this->user();

        return $this->failure;
    }

    /**
     * Validate a token without remembering it as the current request's credential.
     */
    public function validate(array $credentials = []): bool
    {
        if (empty($credentials['token'])) {
            return false;
        }

        try {
            $this->authenticator->authenticate((string) $credentials['token'], $this->currentRequest());
        } catch (AdminAuthException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Forget the resolved admin, so the next call authenticates the bearer token again.
     */
    public function forgetUser(): static
    {
        $this->forgetResolution();

        return $this;
    }

    /**
     * Use another request from now on.
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;

        $this->forgetResolution();

        return $this;
    }

    /**
     * The request being handled: the container's current one, or the one the guard was created for.
     */
    protected function currentRequest(): Request
    {
        $request = app()->bound('request') ? app('request') : null;

        return $request instanceof Request
            ? $request
            : $this->request;
    }

    /**
     * Drop the cached admin, credential and failure.
     */
    protected function forgetResolution(): void
    {
        $this->user = null;
        $this->credential = null;
        $this->failure = null;
        $this->resolvedRequest = null;
        $this->resolvedBearer = null;
    }
}
