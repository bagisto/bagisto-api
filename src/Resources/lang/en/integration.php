<?php

return [
    'menu' => [
        'title' => 'Integration',
    ],

    'acl' => [
        'title'      => 'Integration',
        'create'     => 'Create Integration',
        'edit'       => 'Edit Integration',
        'delete'     => 'Revoke Integration Token',
        'generate'   => 'Generate Integration Token',
        'regenerate' => 'Regenerate Integration Token',
    ],

    'index' => [
        'title'      => 'Integrations',
        'create-btn' => 'Create Integration',
    ],

    'create' => [
        'title'    => 'Create Integration',
        'save-btn' => 'Save',
        'back-btn' => 'Back',
    ],

    'edit' => [
        'title'          => 'Edit Integration',
        'save-btn'       => 'Save',
        'back-btn'       => 'Back',
        'generate-btn'   => 'Generate Token',
        'regenerate-btn' => 'Regenerate Token',
        'revoke-btn'     => 'Revoke Token',
        'copy-btn'       => 'Copy',
        'token-warning'  => 'Save this token now — it will not be shown again.',
        'token-label'    => 'Token',
        'not-generated'  => 'Not generated yet',
        'masked'         => '(Stored — only shown once at generation)',
        'history-banner' => 'This token is no longer active.',
    ],

    'fields' => [
        'name'                  => 'Name',
        'description'           => 'Description',
        'assign-user'           => 'Assign User',
        'permission-type'       => 'Permission Type',
        'access-control'        => 'Access Control',
        'general'               => 'General',
        'token-settings'        => 'Token Settings',
        'valid-till'            => 'Valid Till',
        'rate-limit-per-minute' => 'Rate Limit (per minute)',
        'rate-limit-per-day'    => 'Rate Limit (per day)',
        'never-expires'         => 'Never expires',
        'expires-on'            => 'Expires on',
        'unlimited'             => 'Unlimited',
        'limit-to'              => 'Limit to',
        'requests-per-minute'   => 'requests / minute',
        'requests-per-day'      => 'requests / day',
        'select-admin'          => 'Select an admin',
        'no-available-admins'   => 'No admins available — every admin already has an active token.',
        'same-as-web-hint'      => 'Token will mirror the assigned admin\'s current role permissions live.',
    ],

    'permission_type' => [
        'all'         => 'All',
        'custom'      => 'Custom',
        'same_as_web' => 'Same as Web Permission',
    ],

    'status' => [
        'draft'       => 'Draft',
        'active'      => 'Active',
        'revoked'     => 'Revoked',
        'regenerated' => 'Regenerated',
    ],

    'datagrid' => [
        'id'              => 'ID',
        'name'            => 'Name',
        'admin'           => 'Admin',
        'token'           => 'Token',
        'status'          => 'Status',
        'permission-type' => 'Permission Type',
        'expires-at'      => 'Valid Till',
        'last-used-at'    => 'Last Used',
        'created-at'      => 'Created At',
        'edit'            => 'Edit',
        'revoke'          => 'Revoke',
    ],

    'messages' => [
        'draft-created'          => 'Integration created. Generate the token to start using it.',
        'updated'                => 'Integration updated successfully.',
        'generated'              => 'Token generated. Copy it now — it will not be shown again.',
        'regenerated'            => 'Token regenerated. Copy the new token now — it will not be shown again.',
        'revoked'                => 'Token revoked successfully.',
        'generate-only-draft'    => 'Only draft integrations can have their token generated.',
        'regenerate-only-active' => 'Only active tokens can be regenerated.',
        'cannot-edit-historic'   => 'Revoked or regenerated tokens cannot be edited.',
        'already-inactive'       => 'This token is already inactive.',
    ],

    'errors' => [
        'admin-has-token' => 'Selected admin already has an active integration token.',
    ],

    'configuration' => [
        'api' => [
            'title' => 'API',
            'info'  => 'Settings for the Bagisto API and its admin modules.',
        ],
        'integration' => [
            'title' => 'Integration',
            'info'  => 'Manage the API Integration plugin used to issue admin API tokens.',
        ],
        'settings' => [
            'title'  => 'Module Settings',
            'info'   => 'Enable or disable the API Integration plugin. When disabled, its sidebar menu is hidden and its pages return 404.',
            'enable' => 'Enable API Integration Module',
        ],
    ],

    'emails' => [
        'generated' => [
            'subject'  => 'A new API token was generated: :name',
            'greeting' => 'An API integration token named ":name" was just generated on your account.',
        ],
        'regenerated' => [
            'subject'  => 'Your API token was regenerated: :name',
            'greeting' => 'The API integration token named ":name" was just regenerated. The previous token has stopped working — only the new one is valid.',
        ],
        'revoked' => [
            'subject'  => 'Your API token was revoked: :name',
            'greeting' => 'The API integration token named ":name" was revoked. Any client using it has lost access.',
        ],

        'details' => [
            'name' => 'Token Name',
            'date' => 'Date',
            'ip'   => 'From IP',
        ],

        'revoke-hint'   => 'If you did not expect this, revoke the token immediately using the button below.',
        'revoke-btn'    => 'Revoke This Token',
        'revoke-expiry' => 'This revoke link is valid for 7 days. After that, sign in to the admin panel to manage the token.',
        'no-action'     => 'No action is needed — this email is only a confirmation.',
    ],

    'revoke-confirmation' => [
        'title'                    => 'Revoke API Token',
        'success-title'            => 'Token Revoked',
        'success-message'          => 'The token ":name" has been revoked. Any client using it has lost access immediately.',
        'already-inactive-title'   => 'Token Already Inactive',
        'already-inactive-message' => 'The token ":name" was already revoked or regenerated. No further action is needed.',
    ],

    'confirm' => [
        'generate' => [
            'title'   => 'Generate Token',
            'message' => 'Generate the token now? The plaintext will be shown only once — copy it before leaving the page.',
        ],
        'regenerate' => [
            'title'   => 'Regenerate Token',
            'message' => 'Regenerate the token? The old token will stop working immediately and the new plaintext will be shown only once.',
        ],
        'revoke' => [
            'title'   => 'Revoke Token',
            'message' => 'Revoke this token? Any client using it will lose access immediately. This action cannot be undone.',
        ],
    ],
];
