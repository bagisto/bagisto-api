<?php

/**
 * System configuration for the BagistoApi admin modules.
 *
 * Merged into the core `system` config so the entries appear under
 * Admin → Configuration. The `api.integration.settings.enabled` flag controls
 * whether the "API Integration" plugin is shown in the admin sidebar.
 */
return [
    [
        'key'  => 'api',
        'name' => 'bagistoapi::app.integration.configuration.api.title',
        'info' => 'bagistoapi::app.integration.configuration.api.info',
        'sort' => 6,
    ], [
        'key'  => 'api.integration',
        'name' => 'bagistoapi::app.integration.configuration.integration.title',
        'info' => 'bagistoapi::app.integration.configuration.integration.info',
        'icon' => 'settings/store.svg',
        'sort' => 1,
    ], [
        'key'    => 'api.integration.settings',
        'name'   => 'bagistoapi::app.integration.configuration.settings.title',
        'info'   => 'bagistoapi::app.integration.configuration.settings.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'enabled',
                'title'   => 'bagistoapi::app.integration.configuration.settings.enable',
                'type'    => 'boolean',
                'default' => true,
            ],
        ],
    ],
];
