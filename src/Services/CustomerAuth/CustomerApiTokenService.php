<?php

namespace Webkul\BagistoApi\Services\CustomerAuth;

use Carbon\CarbonInterface;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Services\Jwt\JwtSigner;
use Webkul\BagistoApi\Models\CustomerApiRefreshToken;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Repositories\CustomerApiRefreshTokenRepository;
use Webkul\BagistoApi\Repositories\CustomerApiSessionRepository;
use Webkul\Customer\Contracts\Customer;

class CustomerApiTokenService
{
    /**
     * The number of random bytes in a refresh token's secret.
     */
    public const REFRESH_SECRET_BYTES = 32;

    /**
     * Create a new customer token service.
     */
    public function __construct(
        protected CustomerApiSessionRepository $sessionRepository,
        protected CustomerApiRefreshTokenRepository $refreshTokenRepository,
        protected CustomerApiAuthSettings $settings,
        protected JwtSigner $signer,
    ) {}

    /**
     * Start a session for a customer on a channel and issue its first access and refresh tokens.
     *
     * @param  array{name?: ?string, client_type?: ?string, ip_address?: ?string, user_agent?: ?string, channel?: ?string}  $client
     */
    public function issue(Customer $customer, int $channelId, array $client = []): CustomerApiTokenPair
    {
        Event::dispatch('bagistoapi.customer.api-session.create.before', $customer);

        $now = Carbon::now();

        $expiresAt = $now->copy()->addDays($this->settings->sessionLifetime());

        $session = $this->sessionRepository->create([
            'customer_id' => $customer->id,
            'channel_id' => $channelId,
            'name' => $this->limit($client['name'] ?? null, 100),
            'client_type' => $client['client_type'] ?? null,
            'ip_address' => $client['ip_address'] ?? null,
            'user_agent' => $this->limit($client['user_agent'] ?? null, 1023),
            'last_ip_address' => $client['ip_address'] ?? null,
            'last_user_agent' => $this->limit($client['user_agent'] ?? null, 1023),
            'last_used_at' => $now,
            'last_channel' => $this->channel($client['channel'] ?? null),
            'refresh_expires_at' => $this->refreshExpiry($now, $expiresAt),
            'expires_at' => $expiresAt,
        ]);

        $this->enforceSessionLimit($customer->id);

        $pair = $this->issuePair($session, $now);

        Event::dispatch('bagistoapi.customer.api-session.create.after', $session);

        return $pair;
    }

    /**
     * Exchange a refresh token for a new pair; a reused token revokes the whole session.
     *
     * @param  array{ip_address?: ?string, user_agent?: ?string, channel?: ?string}  $client
     *
     * @throws InvalidCustomerRefreshTokenException
     */
    public function rotate(string $refreshToken, array $client = []): CustomerApiTokenPair
    {
        $token = $this->findRefreshToken($refreshToken);

        $session = $token->session;

        if ($token->isUsed()) {
            $this->revokeForReuse($session);
        }

        if (
            ! $session->isActive()
            || $token->isExpired()
            || ! $this->isCustomerActive($session)
        ) {
            throw new InvalidCustomerRefreshTokenException(session: $session);
        }

        $pair = $this->refreshTokenRepository->transaction(fn () => $this->claimAndReplace($token, $session, $client));

        if (! $pair) {
            $this->revokeForReuse($session);
        }

        Event::dispatch('bagistoapi.customer.api-session.refresh.after', $pair->session);

        return $pair;
    }

    /**
     * Revoke one session, which invalidates its access and refresh tokens at once.
     */
    public function revoke(CustomerApiSession $session, string $reason): CustomerApiSession
    {
        Event::dispatch('bagistoapi.customer.api-session.revoke.before', $session);

        $session = $this->sessionRepository->revoke($session, $reason);

        Event::dispatch('bagistoapi.customer.api-session.revoke.after', $session);

        return $session;
    }

    /**
     * Revoke every live session of a customer, optionally keeping one, and return how many were revoked.
     */
    public function revokeAllForCustomer(int $customerId, string $reason, ?int $exceptSessionId = null): int
    {
        Event::dispatch('bagistoapi.customer.api-session.revoke-all.before', $customerId);

        $revoked = $this->sessionRepository->revokeAllForCustomer($customerId, $reason, $exceptSessionId);

        Event::dispatch('bagistoapi.customer.api-session.revoke-all.after', $customerId);

        return $revoked;
    }

    /**
     * Whether a session may still authenticate requests: live, inside its lifetime, with an active customer.
     */
    public function isSessionUsable(CustomerApiSession $session): bool
    {
        return $session->isActive()
            && $this->isCustomerActive($session);
    }

    /**
     * Delete expired refresh tokens and long-ended sessions.
     *
     * @return array{refresh_tokens: int, sessions: int}
     */
    public function prune(): array
    {
        $now = Carbon::now();

        return [
            'refresh_tokens' => $this->refreshTokenRepository->deleteExpiredBefore($now),
            'sessions' => $this->sessionRepository->deleteEndedBefore(
                $now->copy()->subDays($this->settings->pruneAfterDays())
            ),
        ];
    }

    /**
     * Run a callback in the same transaction as the session's writes.
     */
    public function transaction(Closure $callback): mixed
    {
        return $this->sessionRepository->transaction($callback);
    }

    /**
     * The session a plain refresh token belongs to, or null when it names no token or its secret does not match.
     */
    public function sessionIdFor(string $plainRefreshToken): ?int
    {
        try {
            return (int) $this->findRefreshToken($plainRefreshToken)->session_id;
        } catch (InvalidCustomerRefreshTokenException) {
            return null;
        }
    }

    /**
     * End a customer's oldest live sessions once they hold more than the configured limit.
     */
    protected function enforceSessionLimit(int $customerId): void
    {
        $limit = $this->settings->maxSessions();

        if ($limit === 0) {
            return;
        }

        $ids = $this->sessionRepository->idsBeyondNewest($customerId, $limit);

        if ($ids === []) {
            return;
        }

        Event::dispatch('bagistoapi.customer.api-session.revoke-all.before', $customerId);

        $this->sessionRepository->revokeMany($ids, CustomerApiSession::REVOKED_SESSION_LIMIT);

        Event::dispatch('bagistoapi.customer.api-session.revoke-all.after', $customerId);
    }

    /**
     * Issue an access token and a new refresh token inside a session.
     */
    protected function issuePair(CustomerApiSession $session, CarbonInterface $now): CustomerApiTokenPair
    {
        [$refreshToken, $plainRefreshToken] = $this->createRefreshToken($session);

        $accessExpiresAt = $now->copy()->addMinutes($this->settings->accessTokenTtl());

        if ($accessExpiresAt->greaterThan($session->expires_at)) {
            $accessExpiresAt = $session->expires_at->copy();
        }

        return new CustomerApiTokenPair(
            accessToken: $this->signer->signCustomerAccessToken($session->customer_id, $session->id, $now, $accessExpiresAt),
            accessTokenExpiresAt: $accessExpiresAt,
            refreshToken: $plainRefreshToken,
            refreshTokenId: $refreshToken->id,
            refreshTokenExpiresAt: $refreshToken->expires_at,
            session: $session,
            issuedAt: $now,
        );
    }

    /**
     * Create a refresh token for a session and return it with its plain `<id>|<secret>` value.
     *
     * @return array{0: CustomerApiRefreshToken, 1: string}
     */
    protected function createRefreshToken(CustomerApiSession $session): array
    {
        $secret = bin2hex(random_bytes(self::REFRESH_SECRET_BYTES));

        $refreshToken = $this->refreshTokenRepository->create([
            'session_id' => $session->id,
            'token' => $this->hash($secret),
            'expires_at' => $session->refresh_expires_at,
        ]);

        return [$refreshToken, $refreshToken->id.'|'.$secret];
    }

    /**
     * Find the refresh token a plain value names, when its secret matches.
     *
     * @throws InvalidCustomerRefreshTokenException
     */
    protected function findRefreshToken(string $plain): CustomerApiRefreshToken
    {
        $parts = explode('|', $plain, 2);

        if (
            count($parts) !== 2
            || ! ctype_digit($parts[0])
            || $parts[1] === ''
        ) {
            throw new InvalidCustomerRefreshTokenException;
        }

        $token = $this->refreshTokenRepository->findWithSession((int) $parts[0]);

        if (
            ! $token
            || ! $token->session
            || ! hash_equals($token->token, $this->hash($parts[1]))
        ) {
            throw new InvalidCustomerRefreshTokenException;
        }

        return $token;
    }

    /**
     * Claim a refresh token and issue the pair that replaces it, or return null when another request claimed it first.
     *
     * @param  array{ip_address?: ?string, user_agent?: ?string, channel?: ?string}  $client
     */
    protected function claimAndReplace(CustomerApiRefreshToken $token, CustomerApiSession $session, array $client): ?CustomerApiTokenPair
    {
        if (! $this->refreshTokenRepository->claim($token->id)) {
            return null;
        }

        Event::dispatch('bagistoapi.customer.api-session.refresh.before', $session);

        $now = Carbon::now();

        $session = $this->sessionRepository->touchUsage($session, [
            'refresh_expires_at' => $this->refreshExpiry($now, $session->expires_at),
            'last_ip_address' => $client['ip_address'] ?? $session->last_ip_address,
            'last_user_agent' => $this->limit($client['user_agent'] ?? $session->last_user_agent, 1023),
            'last_used_at' => $now,
            'last_channel' => $this->channel($client['channel'] ?? $session->last_channel),
        ]);

        $pair = $this->issuePair($session, $now);

        $this->refreshTokenRepository->markReplacedBy($token->id, $pair->refreshTokenId);

        return $pair;
    }

    /**
     * Revoke the session a used refresh token was presented for, and reject the request.
     *
     * @throws InvalidCustomerRefreshTokenException
     */
    protected function revokeForReuse(CustomerApiSession $session): never
    {
        if (! $session->isRevoked()) {
            $this->revoke($session, CustomerApiSession::REVOKED_REFRESH_REUSE);
        }

        throw new InvalidCustomerRefreshTokenException(reused: true, session: $session);
    }

    /**
     * The refresh expiry for a token issued now, never past the session's absolute lifetime.
     */
    protected function refreshExpiry(CarbonInterface $now, CarbonInterface $sessionExpiresAt): CarbonInterface
    {
        $expiresAt = $now->copy()->addDays($this->settings->refreshTokenTtl());

        return $expiresAt->greaterThan($sessionExpiresAt)
            ? $sessionExpiresAt->copy()
            : $expiresAt;
    }

    /**
     * Whether the session's customer exists and is active, as core's storefront login requires.
     */
    protected function isCustomerActive(CustomerApiSession $session): bool
    {
        return $session->customer !== null
            && (bool) $session->customer->status;
    }

    /**
     * A known channel, or null.
     */
    protected function channel(?string $channel): ?string
    {
        return in_array($channel, CustomerApiSession::CHANNELS, true)
            ? $channel
            : null;
    }

    /**
     * Truncate a client-supplied value to its column length.
     */
    protected function limit(?string $value, int $length): ?string
    {
        return $value === null
            ? null
            : Str::limit($value, $length, '');
    }

    /**
     * The stored hash of a refresh token secret.
     */
    protected function hash(string $secret): string
    {
        return hash('sha256', $secret);
    }
}
