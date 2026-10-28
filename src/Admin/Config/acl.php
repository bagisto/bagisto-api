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
        'route' => 'admin.integration.create',
        'sort' => 2,
    ], [
        'key' => 'integration.edit',
        'name' => 'bagistoapi::app.integration.acl.edit',
        'route' => 'admin.integration.edit',
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
        'route' => 'admin.integration.history.index',
        'sort' => 1,
    ], [
        'key' => 'integration.history.delete',
        'name' => 'bagistoapi::app.integration.history.acl.delete',
        'route' => 'admin.integration.history.mass_delete',
        'sort' => 2,
    ],
];
