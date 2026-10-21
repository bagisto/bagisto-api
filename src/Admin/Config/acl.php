<?php

return [
    [
        'key' => 'integration',
        'name' => 'bagistoapi::app.integration.acl.title',
        'route' => 'admin.integration.index',
        'sort' => 10,
    ], [
        'key' => 'integration.view',
        'name' => 'bagistoapi::app.integration.acl.view',
        'route' => 'admin.integration.token.index',
        'sort' => 1,
    ], [
        'key' => 'integration.create',
        'name' => 'bagistoapi::app.integration.acl.create',

        'route' => [
            'admin.integration.create',
            'admin.integration.store',
        ],

        'sort' => 2,
    ], [
        'key' => 'integration.edit',
        'name' => 'bagistoapi::app.integration.acl.edit',

        'route' => [
            'admin.integration.edit',
            'admin.integration.update',
        ],

        'sort' => 3,
    ], [
        'key' => 'integration.delete',
        'name' => 'bagistoapi::app.integration.acl.delete',
        'route' => 'admin.integration.destroy',
        'sort' => 4,
    ], [
        'key' => 'integration.generate',
        'name' => 'bagistoapi::app.integration.acl.generate',
        'route' => 'admin.integration.generate',
        'sort' => 5,
    ], [
        'key' => 'integration.regenerate',
        'name' => 'bagistoapi::app.integration.acl.regenerate',
        'route' => 'admin.integration.regenerate',
        'sort' => 6,
    ], [
        'key' => 'integration.history',
        'name' => 'bagistoapi::app.integration.history.acl.title',
        'route' => 'admin.integration.history.index',
        'sort' => 7,
    ], [
        'key' => 'integration.history.view',
        'name' => 'bagistoapi::app.integration.history.acl.view',

        'route' => [
            'admin.integration.history.index',
            'admin.integration.history.view',
        ],

        'sort' => 1,
    ], [
        'key' => 'integration.history.delete',
        'name' => 'bagistoapi::app.integration.history.acl.delete',

        'route' => [
            'admin.integration.history.mass_delete',
            'admin.integration.history.cleanup',
        ],

        'sort' => 2,
    ], [
        'key' => 'settings.api_sessions',
        'name' => 'bagistoapi::app.admin.auth.acl.api-sessions',
        'route' => 'admin.settings.api_sessions.index',
        'sort' => 11,
    ], [
        'key' => 'settings.api_sessions.delete',
        'name' => 'bagistoapi::app.admin.auth.acl.revoke',

        'route' => [
            'admin.settings.api_sessions.delete',
            'admin.settings.api_sessions.revoke_all',
        ],

        'sort' => 1,
    ],
];
