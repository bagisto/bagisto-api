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
                'type' => 'number',
                'default' => 60,
                'validation' => 'required|integer|min:1',
            ], [
                'name' => 'api_token_requests_per_minute',
                'title' => 'bagistoapi::app.admin.auth.configuration.rate-limits.api-token-requests-per-minute',
                'type' => 'number',
                'default' => 60,
                'validation' => 'required|integer|min:1',
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
    ],
];
