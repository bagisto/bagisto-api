<?php

return [
    [
        'key'        => 'integration',
        'name'       => 'bagistoapi::app.integration.menu.title',
        'route'      => 'admin.integration.index',
        'sort'       => 8,
        'icon'       => 'icon-settings',
        'icon-class' => 'settings-icon',
    ], [
        'key'   => 'integration.tokens',
        'name'  => 'bagistoapi::app.integration.menu.tokens',
        'route' => 'admin.integration.token.index',
        'sort'  => 1,
        'icon'  => '',
    ], [
        'key'   => 'integration.history',
        'name'  => 'bagistoapi::app.integration.history.menu.title',
        'route' => 'admin.integration.history.index',
        'sort'  => 2,
        'icon'  => '',
    ],
];
