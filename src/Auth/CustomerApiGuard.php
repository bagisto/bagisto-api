<?php

namespace Webkul\BagistoApi\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;
use Webkul\BagistoApi\Exception\CustomerAuthException;
use Webkul\BagistoApi\Models\CustomerApiSession;

class CustomerApiGuard implements Guard
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
     * The session the current request authenticated with.
     */
    protected ?CustomerApiSession $session = null;

    /**
     * Why the current request's bearer token was rejected.
     */
    protected ?CustomerAuthException $failure = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        UserProvider $provider,
        Request $request,
        protected CustomerApiAuthenticator $authenticator,
    ) {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * The customer behind the request's access token, resolved once per request and bearer token.
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
            || ! static::looksLikeAccessToken($bearer)
        ) {
            return null;
        }

        return $this->user = $this->resolve($bearer, $request);
    }

    /**
     * The session the current request authenticated with.
     */
    public function session(): ?CustomerApiSession
    {
        $this->user();

        return $this->session;
    }

    /**
     * Why the current request's bearer token was rejected, or null when it was accepted or absent.
     */
    public function failure(): ?CustomerAuthException
    {
        $this->user();

        return $this->failure;
    }

    /**
     * The customer behind a token that did not arrive as the request's bearer, without caching it.
     */
    public function customerForToken(string $token): ?Authenticatable
    {
        if (! static::looksLikeAccessToken($token)) {
            return null;
        }

        try {
            return $this->authenticator->authenticate($token, $this->currentRequest())->customer;
        } catch (CustomerAuthException $exception) {
            return null;
        }
    }

    /**
     * Validate a token without remembering it as the current request's credential.
     */
    public function validate(array $credentials = []): bool
    {
        return ! empty($credentials['token'])
            && $this->customerForToken((string) $credentials['token']) !== null;
    }

    /**
     * Forget the resolved customer, so the next call authenticates the bearer token again.
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
     * Whether a bearer value has the three dot-separated parts of a JWT, so a guest cart token is never verified.
     */
    public static function looksLikeAccessToken(string $token): bool
    {
        return substr_count($token, '.') === 2;
    }

    /**
     * Whether an unverified token says it is a customer access token, read from its payload without checking it.
     */
    public static function claimsCustomerAccess(string $token): bool
    {
        if (! static::looksLikeAccessToken($token)) {
            return false;
        }

        $payload = json_decode((string) base64_decode(strtr(explode('.', $token)[1], '-_', '+/')), true);

        return is_array($payload)
            && ($payload['typ'] ?? null) === JwtSigner::TYPE_CUSTOMER_ACCESS;
    }

    /**
     * Authenticate the bearer token, remembering the session or the failure.
     */
    protected function resolve(string $bearer, Request $request): ?Authenticatable
    {
        try {
            $this->session = $this->authenticator->authenticate($bearer, $request);
        } catch (CustomerAuthException $exception) {
            $this->failure = $exception;

            return null;
        }

        return $this->session->customer;
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
     * Drop the cached customer, session and failure.
     */
    protected function forgetResolution(): void
    {
        $this->user = null;
        $this->session = null;
        $this->failure = null;
        $this->resolvedRequest = null;
        $this->resolvedBearer = null;
    }
}
