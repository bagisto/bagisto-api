<?php

use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Models\AdminApiRefreshToken;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Models\AdminApiTwoFactorChallenge;
use Webkul\Core\Models\CoreConfig;

return [
    /**
     * Master switch. When false, no admin-API change is recorded.
     */
    'enabled' => env('BAGISTO_API_AUDIT_ENABLED', true),

    /**
     * Field names whose values are never stored; matching keys are replaced with "[redacted]".
     */
    'redact' => [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'api_token',
        'two_factor_backup_codes',
        'secret',
        '*_token',
        '*_secret',
    ],

    /**
     * Substrings that mark an attribute key, or a row's `code`, as secret, whatever the model.
     */
    'redact_contains' => [
        'secret',
        'password',
        'key',
        'token',
    ],

    /**
     * Models whose `value` column is never stored in clear text.
     */
    'redact_value_models' => [
        CoreConfig::class,
    ],

    /**
     * Models that are never audited, to avoid self-auditing and noise.
     */
    'exclude_models' => [
        AdminApiAudit::class,
        AdminApiSession::class,
        AdminApiRefreshToken::class,
        AdminApiToken::class,
        AdminApiTwoFactorChallenge::class,
    ],

    /**
     * Days `bagisto-api:prune-audits` keeps audit rows for, or null to keep them forever. Used when
     * Configuration → API → Admin Authentication → Data Retention leaves the field empty.
     */
    'retention_days' => env('BAGISTO_API_AUDIT_RETENTION_DAYS', null),
];
