<?php

namespace Webkul\BagistoApi\Admin\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Audit\AdminApiAuthAuditor;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Repositories\AdminApiSessionRepository;
use Webkul\BagistoApi\Admin\Repositories\AdminApiTokenRepository;
use Webkul\BagistoApi\Admin\Services\DomainBinding;
use Webkul\BagistoApi\Admin\Services\Jwt\ExpiredJwtException;
use Webkul\BagistoApi\Admin\Services\Jwt\InvalidJwtException;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtVerifier;
use Webkul\BagistoApi\Exception\AdminAuthException;
use Webkul\User\Models\Admin;

class AdminApiAuthenticator
{
    /**
     * Seconds between two writes of a session's or API token's last-used details from the same address.
     */
    public const USAGE_WRITE_INTERVAL_SECONDS = 60;

    /**
     * Create a new authenticator.
     */
    public function __construct(
        protected JwtVerifier $verifier,
        protected AdminApiSessionRepository $sessionRepository,
        protected AdminApiTokenRepository $apiTokenRepository,
        protected DomainBinding $domainBinding,
        protected AdminApiAuthAuditor $auditor,
    ) {}

    /**
     * Authenticate a bearer access token or API token and record where it was used from.
     *
     * @throws AdminAuthException
     */
    public function authenticate(string $bearer, Request $request): AdminApiCredential
    {
        $claims = $this->verifier->verify($bearer);

        $credential = $claims['typ'] === JwtSigner::TYPE_ACCESS
            ? $this->authenticateSession($claims)
            : $this->authenticateApiToken($claims, $request);

        $this->recordUsage($credential, $request);

        return $credential;
    }

    /**
     * Resolve an access token's live session, rejecting a revoked or expired session or an inactive admin.
     *
     * @throws InvalidJwtException
     */
    protected function authenticateSession(array $claims): AdminApiCredential
    {
        $session = $this->sessionRepository->findWithAdmin($claims['sid']);

        if (
            ! $session
            || (int) $session->admin_id !== $claims['sub']
            || ! $session->isActive()
            || ! $this->isActiveAdmin($session->admin)
        ) {
            throw new InvalidJwtException;
        }

        return AdminApiCredential::forSession(
            $session->admin,
            $session,
            Carbon::createFromTimestamp($claims['exp']),
        );
    }

    /**
     * Resolve an API token by its id, rejecting it when revoked, expired, used from another domain or its admin is inactive.
     *
     * @throws AdminAuthException
     */
    protected function authenticateApiToken(array $claims, Request $request): AdminApiCredential
    {
        $token = $this->apiTokenRepository->findByJti($claims['jti']);

        if (
            ! $token
            || (int) $token->admin_id !== $claims['sub']
            || $token->domain !== $claims['aud']
        ) {
            throw new InvalidJwtException;
        }

        if ($token->isRevoked()) {
            throw new AdminAuthException(
                trans('bagistoapi::app.admin.auth.errors.api-token-revoked'),
                AdminAuthException::CODE_API_TOKEN_REVOKED,
            );
        }

        if ($token->isExpired()) {
            throw new ExpiredJwtException(JwtSigner::TYPE_API);
        }

        if (! $this->domainBinding->matches($token, $request)) {
            $this->rejectDomainMismatch($token, $request);
        }

        if (! $this->isActiveAdmin($token->admin)) {
            throw new InvalidJwtException;
        }

        return AdminApiCredential::forApiToken($token->admin, $token);
    }

    /**
     * Count and audit a request from the wrong domain, then refuse it.
     *
     * @throws AdminAuthException
     */
    protected function rejectDomainMismatch(AdminApiToken $token, Request $request): never
    {
        $token = $this->domainBinding->recordMismatch($token);

        $this->auditor->record(AdminApiAuthAuditor::EVENT_DOMAIN_MISMATCH, $token->admin, $token, $request, [
            'domain' => $token->domain,
            'ip_address' => $request->ip(),
            'origin' => $request->headers->get('Origin') ?: $request->headers->get('Referer'),
            'mismatch_count' => $token->domain_mismatch_count,
        ]);

        throw new AdminAuthException(
            trans('bagistoapi::app.admin.auth.errors.api-token-domain-mismatch'),
            AdminAuthException::CODE_API_TOKEN_DOMAIN_MISMATCH,
            403,
        );
    }

    /**
     * Store the last-used details, at most once a minute unless the client address changed.
     */
    protected function recordUsage(AdminApiCredential $credential, Request $request): void
    {
        $model = $credential->session ?? $credential->apiToken;

        $ip = $request->ip();

        if (
            $model->last_used_at !== null
            && $model->last_used_at->greaterThan(Carbon::now()->subSeconds(self::USAGE_WRITE_INTERVAL_SECONDS))
            && $model->last_ip_address === $ip
        ) {
            return;
        }

        $usage = [
            'last_used_at' => Carbon::now(),
            'last_ip_address' => $ip,
            'last_user_agent' => Str::limit((string) $request->userAgent(), 1023, '') ?: null,
            'last_channel' => AdminAuthHelper::channel($request),
        ];

        if ($model instanceof AdminApiSession) {
            $this->sessionRepository->touchUsage($model, $usage);

            return;
        }

        $this->apiTokenRepository->touchUsage($model, $usage);
    }

    /**
     * Whether the admin exists and is active.
     */
    protected function isActiveAdmin(?Admin $admin): bool
    {
        return $admin !== null
            && (bool) $admin->status;
    }
}
