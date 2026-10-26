<?php

return [
    [
        'key' => 'api',
        'name' => 'bagistoapi::app.admin.api-configuration.title',
        'info' => 'bagistoapi::app.admin.api-configuration.info',
        'sort' => 6,
    ], [
        'key' => 'api.admin_auth',
        'name' => 'bagistoapi::app.admin.auth.configuration.title',
        'info' => 'bagistoapi::app.admin.auth.configuration.info',
        'icon' => 'settings/store.svg',
        'sort' => 1,
    ], [
        'key' => 'api.admin_auth.tokens',
        'name' => 'bagistoapi::app.admin.auth.configuration.tokens.title',
        'info' => 'bagistoapi::app.admin.auth.configuration.tokens.info',
        'sort' => 1,

        'fields' => [
            [
                'name' => 'access_token_ttl',
                'title' => 'bagistoapi::app.admin.auth.configuration.tokens.access-token-ttl',
                'info' => 'bagistoapi::app.admin.auth.configuration.tokens.access-token-ttl-info',
                'type' => 'number',
                'default' => 15,
                'validation' => 'required|integer|min:5|max:60',
            ], [
                'name' => 'refresh_token_ttl',
                'title' => 'bagistoapi::app.admin.auth.configuration.tokens.refresh-token-ttl',
                'info' => 'bagistoapi::app.admin.auth.configuration.tokens.refresh-token-ttl-info',
                'type' => 'number',
                'default' => 30,
                'validation' => 'required|integer|min:1|max:90',
            ], [
                'name' => 'session_lifetime',
                'title' => 'bagistoapi::app.admin.auth.configuration.tokens.session-lifetime',
                'info' => 'bagistoapi::app.admin.auth.configuration.tokens.session-lifetime-info',
                'type' => 'number',
                'default' => 90,
                'validation' => 'required|integer|min:1|max:365',
            ],
        ],
    ], [
        'key' => 'api.admin_auth.rate_limits',
        'name' => 'bagistoapi::app.admin.auth.configuration.rate-limits.title',
        'info' => 'bagistoapi::app.admin.auth.configuration.rate-limits.info',
        'sort' => 2,

        'fields' => [
            [
                'name' => 'session_requests_per_minute',
                'title' => 'bagistoapi::app.admin.auth.configuration.rate-limits.session-requests-per-minute',
                'info' => 'bagistoapi::app.admin.auth.configuration.rate-limits.session-requests-per-minute-info',
                'type' => 'number',
                'default' => 60,
                'validation' => 'required|integer|min:1|max:100000',
            ], [
                'name' => 'api_token_requests_per_minute',
                'title' => 'bagistoapi::app.admin.auth.configuration.rate-limits.api-token-requests-per-minute',
                'info' => 'bagistoapi::app.admin.auth.configuration.rate-limits.api-token-requests-per-minute-info',
                'type' => 'number',
                'default' => 60,
                'validation' => 'required|integer|min:1|max:100000',
            ], [
                'name' => 'unauthenticated_requests_per_minute',
                'title' => 'bagistoapi::app.admin.auth.configuration.rate-limits.unauthenticated-requests-per-minute',
                'info' => 'bagistoapi::app.admin.auth.configuration.rate-limits.unauthenticated-requests-per-minute-info',
                'type' => 'number',
                'default' => 60,
                'validation' => 'required|integer|min:10|max:1000',
            ],
        ],
    ], [
        'key' => 'api.admin_auth.security',
        'name' => 'bagistoapi::app.admin.auth.configuration.security.title',
        'info' => 'bagistoapi::app.admin.auth.configuration.security.info',
        'sort' => 3,

        'fields' => [
            [
                'name' => 'revoke_sessions_on_password_change',
                'title' => 'bagistoapi::app.admin.auth.configuration.security.revoke-sessions-on-password-change',
                'type' => 'boolean',
                'default' => true,
            ], [
                'name' => 'revoke_api_tokens_on_password_change',
                'title' => 'bagistoapi::app.admin.auth.configuration.security.revoke-api-tokens-on-password-change',
                'type' => 'boolean',
                'default' => false,
            ], [
                'name' => 'allow_non_expiring_api_tokens',
                'title' => 'bagistoapi::app.admin.auth.configuration.security.allow-non-expiring-api-tokens',
                'type' => 'boolean',
                'default' => true,
            ], [
                'name' => 'domain_binding_mode',
                'title' => 'bagistoapi::app.admin.auth.configuration.security.domain-binding-mode',
                'info' => 'bagistoapi::app.admin.auth.configuration.security.domain-binding-mode-info',
                'type' => 'select',
                'default' => 'dns',
                'options' => [
                    [
                        'title' => 'bagistoapi::app.admin.auth.configuration.security.domain-binding-dns',
                        'value' => 'dns',
                    ], [
                        'title' => 'bagistoapi::app.admin.auth.configuration.security.domain-binding-origin',
                        'value' => 'origin',
                    ],
                ],
            ],
        ],
    ], [
        'key' => 'api.admin_auth.login_protection',
        'name' => 'bagistoapi::app.admin.auth.configuration.login-protection.title',
        'info' => 'bagistoapi::app.admin.auth.configuration.login-protection.info',
        'sort' => 4,

        'fields' => [
            [
                'name' => 'login_max_attempts',
                'title' => 'bagistoapi::app.admin.auth.configuration.login-protection.login-max-attempts',
                'info' => 'bagistoapi::app.admin.auth.configuration.login-protection.login-max-attempts-info',
                'type' => 'number',
                'default' => 5,
                'validation' => 'required|integer|min:3|max:20',
            ], [
                'name' => 'login_lockout_seconds',
                'title' => 'bagistoapi::app.admin.auth.configuration.login-protection.login-lockout-seconds',
                'info' => 'bagistoapi::app.admin.auth.configuration.login-protection.login-lockout-seconds-info',
                'type' => 'number',
                'default' => 60,
                'validation' => 'required|integer|min:60|max:3600',
            ], [
                'name' => 'two_factor_challenge_lifetime',
                'title' => 'bagistoapi::app.admin.auth.configuration.login-protection.two-factor-challenge-lifetime',
                'info' => 'bagistoapi::app.admin.auth.configuration.login-protection.two-factor-challenge-lifetime-info',
                'type' => 'number',
                'default' => 5,
                'validation' => 'required|integer|min:1|max:15',
            ], [
                'name' => 'two_factor_max_failures',
                'title' => 'bagistoapi::app.admin.auth.configuration.login-protection.two-factor-max-failures',
                'info' => 'bagistoapi::app.admin.auth.configuration.login-protection.two-factor-max-failures-info',
                'type' => 'number',
                'default' => 5,
                'validation' => 'required|integer|min:3|max:20',
            ], [
                'name' => 'password_requests_per_minute',
                'title' => 'bagistoapi::app.admin.auth.configuration.login-protection.password-requests-per-minute',
                'info' => 'bagistoapi::app.admin.auth.configuration.login-protection.password-requests-per-minute-info',
                'type' => 'number',
                'default' => 5,
                'validation' => 'required|integer|min:1|max:20',
            ],
        ],
    ], [
        'key' => 'api.admin_auth.retention',
        'name' => 'bagistoapi::app.admin.auth.configuration.retention.title',
        'info' => 'bagistoapi::app.admin.auth.configuration.retention.info',
        'sort' => 5,

        'fields' => [
            [
                'name' => 'session_prune_days',
                'title' => 'bagistoapi::app.admin.auth.configuration.retention.session-prune-days',
                'info' => 'bagistoapi::app.admin.auth.configuration.retention.session-prune-days-info',
                'type' => 'number',
                'default' => 30,
                'validation' => 'required|integer|min:1|max:365',
            ], [
                'name' => 'audit_retention_days',
                'title' => 'bagistoapi::app.admin.auth.configuration.retention.audit-retention-days',
                'info' => 'bagistoapi::app.admin.auth.configuration.retention.audit-retention-days-info',
                'type' => 'number',
                'validation' => 'nullable|integer|min:0|max:3650',
            ],
        ],
    ], [
        'key' => 'api.customer_auth',
        'name' => 'bagistoapi::app.admin.customer-auth.configuration.title',
        'info' => 'bagistoapi::app.admin.customer-auth.configuration.info',
        'icon' => 'settings/store.svg',
        'sort' => 2,
    ], [
        'key' => 'api.customer_auth.tokens',
        'name' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.title',
        'info' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.info',
        'sort' => 1,

        'fields' => [
            [
                'name' => 'access_token_ttl',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.access-token-ttl',
                'info' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.access-token-ttl-info',
                'type' => 'number',
                'default' => 15,
                'validation' => 'required|integer|min:5|max:60',
            ], [
                'name' => 'refresh_token_ttl',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.refresh-token-ttl',
                'info' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.refresh-token-ttl-info',
                'type' => 'number',
                'default' => 30,
                'validation' => 'required|integer|min:1|max:90',
            ], [
                'name' => 'session_lifetime',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.session-lifetime',
                'info' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.session-lifetime-info',
                'type' => 'number',
                'default' => 90,
                'validation' => 'required|integer|min:1|max:365',
            ], [
                'name' => 'max_sessions',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.max-sessions',
                'info' => 'bagistoapi::app.admin.customer-auth.configuration.tokens.max-sessions-info',
                'type' => 'number',
                'default' => 0,
                'validation' => 'required|integer|min:0|max:100',
            ],
        ],
    ], [
        'key' => 'api.customer_auth.rate_limits',
        'name' => 'bagistoapi::app.admin.customer-auth.configuration.rate-limits.title',
        'info' => 'bagistoapi::app.admin.customer-auth.configuration.rate-limits.info',
        'sort' => 2,

        'fields' => [
            [
                'name' => 'requests_per_minute',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.rate-limits.requests-per-minute',
                'info' => 'bagistoapi::app.admin.customer-auth.configuration.rate-limits.requests-per-minute-info',
                'type' => 'number',
                'default' => 120,
                'validation' => 'required|integer|min:1|max:100000',
            ],
        ],
    ], [
        'key' => 'api.customer_auth.login_protection',
        'name' => 'bagistoapi::app.admin.customer-auth.configuration.login-protection.title',
        'info' => 'bagistoapi::app.admin.customer-auth.configuration.login-protection.info',
        'sort' => 3,

        'fields' => [
            [
                'name' => 'login_max_attempts',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.login-protection.login-max-attempts',
                'info' => 'bagistoapi::app.admin.customer-auth.configuration.login-protection.login-max-attempts-info',
                'type' => 'number',
                'default' => 5,
                'validation' => 'required|integer|min:3|max:20',
            ], [
                'name' => 'login_lockout_seconds',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.login-protection.login-lockout-seconds',
                'info' => 'bagistoapi::app.admin.customer-auth.configuration.login-protection.login-lockout-seconds-info',
                'type' => 'number',
                'default' => 60,
                'validation' => 'required|integer|min:60|max:3600',
            ], [
                'name' => 'refresh_requests_per_minute',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.login-protection.refresh-requests-per-minute',
                'info' => 'bagistoapi::app.admin.customer-auth.configuration.login-protection.refresh-requests-per-minute-info',
                'type' => 'number',
                'default' => 10,
                'validation' => 'required|integer|min:1|max:60',
            ],
        ],
    ], [
        'key' => 'api.customer_auth.security',
        'name' => 'bagistoapi::app.admin.customer-auth.configuration.security.title',
        'info' => 'bagistoapi::app.admin.customer-auth.configuration.security.info',
        'sort' => 4,

        'fields' => [
            [
                'name' => 'revoke_sessions_on_password_change',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.security.revoke-sessions-on-password-change',
                'type' => 'boolean',
                'default' => true,
            ],
        ],
    ], [
        'key' => 'api.customer_auth.retention',
        'name' => 'bagistoapi::app.admin.customer-auth.configuration.retention.title',
        'info' => 'bagistoapi::app.admin.customer-auth.configuration.retention.info',
        'sort' => 5,

        'fields' => [
            [
                'name' => 'session_prune_days',
                'title' => 'bagistoapi::app.admin.customer-auth.configuration.retention.session-prune-days',
                'info' => 'bagistoapi::app.admin.customer-auth.configuration.retention.session-prune-days-info',
                'type' => 'number',
                'default' => 30,
                'validation' => 'required|integer|min:1|max:365',
            ],
        ],
    ],
];
