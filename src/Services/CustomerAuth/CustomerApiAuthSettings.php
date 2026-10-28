<?php

namespace Webkul\BagistoApi\Services\CustomerAuth;

class CustomerApiAuthSettings
{
    /**
     * The system configuration group the customer authentication settings live in.
     */
    public const CONFIG_PREFIX = 'api.customer_auth';

    /**
     * The shortest access token lifetime, in minutes.
     */
    public const ACCESS_TOKEN_TTL_MIN = 5;

    /**
     * The longest access token lifetime, in minutes.
     */
    public const ACCESS_TOKEN_TTL_MAX = 60;

    /**
     * The shortest refresh token lifetime, in days.
     */
    public const REFRESH_TOKEN_TTL_MIN = 1;

    /**
     * The longest refresh token lifetime, in days.
     */
    public const REFRESH_TOKEN_TTL_MAX = 90;

    /**
     * The shortest absolute session lifetime, in days.
     */
    public const SESSION_LIFETIME_MIN = 1;

    /**
     * The longest absolute session lifetime, in days.
     */
    public const SESSION_LIFETIME_MAX = 365;

    /**
     * The most live sessions a customer can be limited to; 0 means no limit.
     */
    public const MAX_SESSIONS_MAX = 100;

    /**
     * The fewest failed logins allowed before a lockout.
     */
    public const LOGIN_MAX_ATTEMPTS_MIN = 3;

    /**
     * The most failed logins allowed before a lockout.
     */
    public const LOGIN_MAX_ATTEMPTS_MAX = 20;

    /**
     * The shortest login lockout, in seconds.
     */
    public const LOGIN_LOCKOUT_SECONDS_MIN = 60;

    /**
     * The longest login lockout, in seconds.
     */
    public const LOGIN_LOCKOUT_SECONDS_MAX = 3600;

    /**
     * The fewest token refreshes allowed per session per minute.
     */
    public const REFRESH_REQUESTS_PER_MINUTE_MIN = 1;

    /**
     * The most token refreshes allowed per session per minute.
     */
    public const REFRESH_REQUESTS_PER_MINUTE_MAX = 60;

    /**
     * The highest per-minute request limit a customer can be given.
     */
    public const REQUESTS_PER_MINUTE_MAX = 100000;

    /**
     * The fewest days a revoked or expired session is kept.
     */
    public const SESSION_PRUNE_DAYS_MIN = 1;

    /**
     * The most days a revoked or expired session is kept.
     */
    public const SESSION_PRUNE_DAYS_MAX = 365;

    /**
     * The access token lifetime in minutes.
     */
    public function accessTokenTtl(): int
    {
        return $this->clamp(
            $this->value('tokens.access_token_ttl', 'access_token_ttl'),
            self::ACCESS_TOKEN_TTL_MIN,
            self::ACCESS_TOKEN_TTL_MAX,
        );
    }

    /**
     * The refresh token lifetime in days.
     */
    public function refreshTokenTtl(): int
    {
        return $this->clamp(
            $this->value('tokens.refresh_token_ttl', 'refresh_token_ttl'),
            self::REFRESH_TOKEN_TTL_MIN,
            self::REFRESH_TOKEN_TTL_MAX,
        );
    }

    /**
     * The absolute session lifetime in days.
     */
    public function sessionLifetime(): int
    {
        return $this->clamp(
            $this->value('tokens.session_lifetime', 'session_lifetime'),
            self::SESSION_LIFETIME_MIN,
            self::SESSION_LIFETIME_MAX,
        );
    }

    /**
     * The live sessions a customer may hold, or 0 for no limit.
     */
    public function maxSessions(): int
    {
        return $this->clamp($this->value('tokens.max_sessions', 'max_sessions'), 0, self::MAX_SESSIONS_MAX);
    }

    /**
     * The failed logins an email address and client address may make before the lockout.
     */
    public function loginMaxAttempts(): int
    {
        return $this->clamp(
            $this->value('login_protection.login_max_attempts', 'login_max_attempts'),
            self::LOGIN_MAX_ATTEMPTS_MIN,
            self::LOGIN_MAX_ATTEMPTS_MAX,
        );
    }

    /**
     * Seconds failed logins are remembered for, and so how long a lockout lasts.
     */
    public function loginLockoutSeconds(): int
    {
        return $this->clamp(
            $this->value('login_protection.login_lockout_seconds', 'login_lockout_seconds'),
            self::LOGIN_LOCKOUT_SECONDS_MIN,
            self::LOGIN_LOCKOUT_SECONDS_MAX,
        );
    }

    /**
     * The token refreshes a session may make in a minute.
     */
    public function refreshRequestsPerMinute(): int
    {
        return $this->clamp(
            $this->value('login_protection.refresh_requests_per_minute', 'refresh_requests_per_minute'),
            self::REFRESH_REQUESTS_PER_MINUTE_MIN,
            self::REFRESH_REQUESTS_PER_MINUTE_MAX,
        );
    }

    /**
     * The requests per minute allowed for each signed-in customer.
     */
    public function requestsPerMinute(): int
    {
        return $this->clamp(
            $this->value('rate_limits.requests_per_minute', 'requests_per_minute'),
            1,
            self::REQUESTS_PER_MINUTE_MAX,
        );
    }

    /**
     * Whether a password change ends the customer's other sessions.
     */
    public function revokeSessionsOnPasswordChange(): bool
    {
        return (bool) $this->value('security.revoke_sessions_on_password_change', 'revoke_sessions_on_password_change');
    }

    /**
     * Days a revoked or expired session is kept before it is pruned.
     */
    public function pruneAfterDays(): int
    {
        return $this->clamp(
            $this->value('retention.session_prune_days', 'prune_after_days'),
            self::SESSION_PRUNE_DAYS_MIN,
            self::SESSION_PRUNE_DAYS_MAX,
        );
    }

    /**
     * The value the admin configured, or the package configuration when there is none.
     */
    protected function value(string $field, string $configKey): mixed
    {
        try {
            $value = core()->getConfigData(self::CONFIG_PREFIX.'.'.$field);
        } catch (\Throwable $exception) {
            $value = null;
        }

        if (
            $value === null
            || $value === ''
        ) {
            return config('bagistoapi.customer_auth.'.$configKey);
        }

        return $value;
    }

    /**
     * Keep a configured number inside its allowed range.
     */
    protected function clamp(mixed $value, int $min, int $max): int
    {
        return min($max, max($min, (int) $value));
    }
}
