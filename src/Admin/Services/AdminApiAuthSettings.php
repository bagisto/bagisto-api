<?php

namespace Webkul\BagistoApi\Admin\Services;

class AdminApiAuthSettings
{
    /**
     * The system configuration group the admin authentication settings live in.
     */
    public const CONFIG_PREFIX = 'api.admin_auth';

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
     * The highest per-minute request limit an admin or API token can be given.
     */
    public const REQUESTS_PER_MINUTE_MAX = 100000;

    /**
     * The fewest per-minute requests allowed without a token, per client address.
     */
    public const UNAUTHENTICATED_REQUESTS_PER_MINUTE_MIN = 10;

    /**
     * The most per-minute requests allowed without a token, per client address.
     */
    public const UNAUTHENTICATED_REQUESTS_PER_MINUTE_MAX = 1000;

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
     * The shortest two-factor challenge lifetime, in minutes.
     */
    public const TWO_FACTOR_CHALLENGE_LIFETIME_MIN = 1;

    /**
     * The longest two-factor challenge lifetime, in minutes.
     */
    public const TWO_FACTOR_CHALLENGE_LIFETIME_MAX = 15;

    /**
     * The fewest wrong two-factor codes allowed per admin per minute.
     */
    public const TWO_FACTOR_MAX_FAILURES_MIN = 3;

    /**
     * The most wrong two-factor codes allowed per admin per minute.
     */
    public const TWO_FACTOR_MAX_FAILURES_MAX = 20;

    /**
     * The fewest forgot and reset password requests allowed per client address per minute.
     */
    public const PASSWORD_REQUESTS_PER_MINUTE_MIN = 1;

    /**
     * The most forgot and reset password requests allowed per client address per minute.
     */
    public const PASSWORD_REQUESTS_PER_MINUTE_MAX = 20;

    /**
     * The fewest days a revoked or expired session is kept.
     */
    public const SESSION_PRUNE_DAYS_MIN = 1;

    /**
     * The most days a revoked or expired session is kept.
     */
    public const SESSION_PRUNE_DAYS_MAX = 365;

    /**
     * The most days the API audit history can be kept; 0 keeps it forever.
     */
    public const AUDIT_RETENTION_DAYS_MAX = 3650;

    /**
     * The domain binding mode that matches the client IP against the domain's DNS records.
     */
    public const BINDING_DNS = 'dns';

    /**
     * The domain binding mode that matches the Origin or Referer host.
     */
    public const BINDING_ORIGIN = 'origin';

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
     * The requests per minute allowed for each signed-in admin.
     */
    public function sessionRequestsPerMinute(): int
    {
        return $this->clamp(
            $this->value('rate_limits.session_requests_per_minute', 'session_requests_per_minute'),
            1,
            self::REQUESTS_PER_MINUTE_MAX,
        );
    }

    /**
     * The requests per minute allowed for each API token.
     */
    public function apiTokenRequestsPerMinute(): int
    {
        return $this->clamp(
            $this->value('rate_limits.api_token_requests_per_minute', 'api_token_requests_per_minute'),
            1,
            self::REQUESTS_PER_MINUTE_MAX,
        );
    }

    /**
     * The requests per minute allowed without a token, for each client address.
     */
    public function unauthenticatedRequestsPerMinute(): int
    {
        return $this->clamp(
            $this->value('rate_limits.unauthenticated_requests_per_minute', 'unauthenticated_requests_per_minute'),
            self::UNAUTHENTICATED_REQUESTS_PER_MINUTE_MIN,
            self::UNAUTHENTICATED_REQUESTS_PER_MINUTE_MAX,
        );
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
     * Minutes a two-factor challenge stays usable.
     */
    public function twoFactorChallengeLifetime(): int
    {
        return $this->clamp(
            $this->value('login_protection.two_factor_challenge_lifetime', 'two_factor_challenge_lifetime'),
            self::TWO_FACTOR_CHALLENGE_LIFETIME_MIN,
            self::TWO_FACTOR_CHALLENGE_LIFETIME_MAX,
        );
    }

    /**
     * The wrong two-factor codes an admin may enter in a minute, across all challenges.
     */
    public function twoFactorMaxFailures(): int
    {
        return $this->clamp(
            $this->value('login_protection.two_factor_max_failures', 'two_factor_max_failures'),
            self::TWO_FACTOR_MAX_FAILURES_MIN,
            self::TWO_FACTOR_MAX_FAILURES_MAX,
        );
    }

    /**
     * The forgot and reset password requests a client address may make in a minute.
     */
    public function passwordRequestsPerMinute(): int
    {
        return $this->clamp(
            $this->value('login_protection.password_requests_per_minute', 'password_requests_per_minute'),
            self::PASSWORD_REQUESTS_PER_MINUTE_MIN,
            self::PASSWORD_REQUESTS_PER_MINUTE_MAX,
        );
    }

    /**
     * Whether a password change revokes the admin's sessions.
     */
    public function revokeSessionsOnPasswordChange(): bool
    {
        return (bool) $this->value('security.revoke_sessions_on_password_change', 'revoke_sessions_on_password_change');
    }

    /**
     * Whether a password change revokes the admin's API tokens.
     */
    public function revokeApiTokensOnPasswordChange(): bool
    {
        return (bool) $this->value('security.revoke_api_tokens_on_password_change', 'revoke_api_tokens_on_password_change');
    }

    /**
     * Whether an API token may be created without an expiry date.
     */
    public function allowNonExpiringApiTokens(): bool
    {
        return (bool) $this->value('security.allow_non_expiring_api_tokens', 'allow_non_expiring_api_tokens');
    }

    /**
     * The domain binding mode, "dns" unless "origin" is configured.
     */
    public function domainBindingMode(): string
    {
        return $this->value('security.domain_binding_mode', 'domain_binding_mode') === self::BINDING_ORIGIN
            ? self::BINDING_ORIGIN
            : self::BINDING_DNS;
    }

    /**
     * Seconds a domain's resolved addresses are cached for.
     */
    public function domainDnsCacheSeconds(): int
    {
        return max(0, (int) config('bagistoapi.admin_auth.domain_dns_cache_seconds', 300));
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
     * Days the API audit history is kept, or null to keep it forever.
     */
    public function auditRetentionDays(): ?int
    {
        try {
            $value = core()->getConfigData(self::CONFIG_PREFIX.'.retention.audit_retention_days');
        } catch (\Throwable $exception) {
            $value = null;
        }

        if (
            $value === null
            || $value === ''
        ) {
            $value = config('bagistoapi.audit.retention_days');
        }

        if (
            $value === null
            || $value === ''
            || ! is_numeric($value)
        ) {
            return null;
        }

        $days = $this->clamp($value, 0, self::AUDIT_RETENTION_DAYS_MAX);

        return $days === 0 ? null : $days;
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
            return config('bagistoapi.admin_auth.'.$configKey);
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
