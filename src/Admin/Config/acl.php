<?php

return [
    [
        'key'   => 'integration',
        'name'  => 'bagistoapi::app.integration.acl.title',
        'route' => 'admin.integration.index',
        'sort'  => 10,
    ], [
        'key'   => 'integration.create',
        'name'  => 'bagistoapi::app.integration.acl.create',
        'route' => 'admin.integration.create',
        'sort'  => 1,
    ], [
        'key'   => 'integration.edit',
        'name'  => 'bagistoapi::app.integration.acl.edit',
        'route' => 'admin.integration.edit',
        'sort'  => 2,
    ], [
        'key'   => 'integration.delete',
        'name'  => 'bagistoapi::app.integration.acl.delete',
        'route' => 'admin.integration.destroy',
        'sort'  => 3,
    ], [
        'key'   => 'integration.generate',
        'name'  => 'bagistoapi::app.integration.acl.generate',
        'route' => 'admin.integration.generate',
        'sort'  => 4,
    ], [
        'key'   => 'integration.regenerate',
        'name'  => 'bagistoapi::app.integration.acl.regenerate',
        'route' => 'admin.integration.regenerate',
        'sort'  => 5,
    ],
];
