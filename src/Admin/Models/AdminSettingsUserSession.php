<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserSessionCollectionProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsUserSession',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/settings/users/{id}/sessions',
            provider: AdminSettingsUserSessionCollectionProvider::class,
            paginationEnabled: false,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Users'],
                summary: "List another admin user's active sessions",
                description: 'Every live login session of the admin user, newest first. Requires `settings.api_sessions`.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Admin ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 5,
                    ]),
                    new Model\Parameter('page', 'query', 'Page (1-based).', false, schema: [
                        'type' => 'integer',
                        'example' => 1,
                    ]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: [
                        'type' => 'integer',
                        'example' => 10,
                    ]),
                    new Model\Parameter('client_type', 'query', 'Filter by client type.', false, schema: [
                        'type' => 'string',
                        'enum' => AdminApiSession::CLIENT_TYPES,
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in { data, meta } envelope.'),
                    '403' => new Model\Response(description: 'The role lacks `settings.api_sessions`.'),
                    '404' => new Model\Response(description: 'Admin not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsUserSessionCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'admin_id' => ['type' => 'Int!'],
                'client_type' => ['type' => 'String'],
            ],
            description: "An admin user's active sessions (cursor pagination). Requires settings.api_sessions.",
        ),
    ],
)]
class AdminSettingsUserSession extends AdminSessionResource {}
