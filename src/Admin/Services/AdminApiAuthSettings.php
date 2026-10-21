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
        return max(1, (int) $this->value('rate_limits.session_requests_per_minute', 'session_requests_per_minute'));
    }

    /**
     * The requests per minute allowed for each API token.
     */
    public function apiTokenRequestsPerMinute(): int
    {
        return max(1, (int) $this->value('rate_limits.api_token_requests_per_minute', 'api_token_requests_per_minute'));
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
        return max(0, (int) config('bagistoapi.admin_auth.prune_after_days', 30));
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
