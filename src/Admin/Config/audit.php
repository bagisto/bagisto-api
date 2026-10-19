<?php

use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\Core\Models\CoreConfig;

/**
 * Admin-API audit trail config (merged under `bagistoapi.audit`).
 */
return [
    /**
     * Master switch. When false, no admin-API change is recorded.
     */
    'enabled' => env('BAGISTO_API_AUDIT_ENABLED', true),

    /**
     * Field names whose values are never stored. Matching keys in
     * old_values / new_values / payload are replaced with "[redacted]".
     */
    'redact' => [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'api_token',
        'secret',
        '*_token',
        '*_secret',
    ],

    /**
     * Substrings that mark an attribute key, or a row's `code`, as secret.
     * Matching attributes are redacted whatever the model.
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
     * Models that are never audited (avoids self-auditing + noise).
     */
    'exclude_models' => [
        AdminApiAudit::class,
        AdminPersonalAccessToken::class,
    ],

    /**
     * Auto-prune cutoff for `bagisto-api:prune-audits`. null = keep forever.
     */
    'retention_days' => env('BAGISTO_API_AUDIT_RETENTION_DAYS', null),
];
