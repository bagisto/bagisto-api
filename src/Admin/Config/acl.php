<?php

return [
    [
        'key' => 'settings.api_tokens',
        'name' => 'bagistoapi::app.admin.api-tokens.acl.title',

        'route' => [
            'admin.settings.api_tokens.index',
            'admin.settings.api_tokens.view',
        ],

        'sort' => 11,
    ], [
        'key' => 'settings.api_tokens.create',
        'name' => 'bagistoapi::app.admin.api-tokens.acl.create',

        'route' => [
            'admin.settings.api_tokens.create',
            'admin.settings.api_tokens.store',
            'admin.settings.api_tokens.update',
        ],

        'sort' => 1,
    ], [
        'key' => 'settings.api_tokens.delete',
        'name' => 'bagistoapi::app.admin.api-tokens.acl.delete',
        'route' => 'admin.settings.api_tokens.delete',
        'sort' => 2,
    ], [
        'key' => 'settings.api_tokens.manage_others',
        'name' => 'bagistoapi::app.admin.api-tokens.acl.manage-others',
        'route' => [],
        'sort' => 3,
    ], [
        'key' => 'settings.api_sessions',
        'name' => 'bagistoapi::app.admin.api-sessions.acl.title',
        'route' => 'admin.settings.api_sessions.index',
        'sort' => 12,
    ], [
        'key' => 'settings.api_sessions.delete',
        'name' => 'bagistoapi::app.admin.api-sessions.acl.delete',

        'route' => [
            'admin.settings.api_sessions.delete',
            'admin.settings.api_sessions.revoke_all',
        ],

        'sort' => 1,
    ], [
        'key' => 'settings.api_audit',
        'name' => 'bagistoapi::app.admin.api-audit.acl.title',

        'route' => [
            'admin.settings.api_audit.index',
            'admin.settings.api_audit.view',
        ],

        'sort' => 13,
    ], [
        'key' => 'settings.api_audit.delete',
        'name' => 'bagistoapi::app.admin.api-audit.acl.delete',

        'route' => [
            'admin.settings.api_audit.mass_delete',
            'admin.settings.api_audit.cleanup',
        ],

        'sort' => 1,
    ],
];
