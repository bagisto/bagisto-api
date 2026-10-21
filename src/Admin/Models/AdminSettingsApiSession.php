<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminSettingsApiSessionCollectionProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminApiSession',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/settings/api-sessions',
            provider: AdminSettingsApiSessionCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: API Sessions'],
                summary: 'List every active admin API session',
                description: 'Every live login session of every admin user, newest first. Requires `settings.api_sessions`.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page (1-based).', false, schema: [
                        'type' => 'integer',
                        'example' => 1,
                    ]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: [
                        'type' => 'integer',
                        'example' => 10,
                    ]),
                    new Model\Parameter('admin_id', 'query', 'Filter by admin ID.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('client_type', 'query', 'Filter by client type.', false, schema: [
                        'type' => 'string',
                        'enum' => AdminApiSession::CLIENT_TYPES,
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in { data, meta } envelope.'),
                    '403' => new Model\Response(description: 'The role lacks `settings.api_sessions`.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsApiSessionCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'admin_id' => ['type' => 'Int'],
                'client_type' => ['type' => 'String'],
            ],
            description: 'Every active admin API session (cursor pagination). Requires settings.api_sessions.',
        ),
    ],
)]
class AdminSettingsApiSession extends AdminSessionResource {}
