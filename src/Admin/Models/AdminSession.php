<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSessionRevokeInput;
use Webkul\BagistoApi\Admin\State\AdminSessionCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSessionProcessor;
use Webkul\BagistoApi\OpenApi\AdminAuthResponses;
use Webkul\BagistoApi\OpenApi\ApiSchemas;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSession',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/auth/sessions',
            provider: AdminSessionCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: "List the signed-in admin's active sessions",
                description: 'Every live login session of the calling admin, newest first, with where each was last used from. `isCurrent` marks the session of the access token sent. Needs a login session access token.',
                parameters: [
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
                    '403' => new Model\Response(description: 'Called with an API token (`access_token_required`).'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/auth/sessions/{id}',
            processor: AdminSessionProcessor::class,
            requirements: ['id' => '\d+'],
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Revoke one of my sessions',
                description: 'Revokes one live session of the calling admin, for example a lost phone; its access and refresh tokens stop working at once. Revoking the current session logs it out.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Session ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 12,
                    ]),
                ],
                responses: [
                    '204' => new Model\Response(description: 'Session revoked; its access and refresh tokens stop working at once.'),
                    '403' => new Model\Response(description: 'Called with an API token (`access_token_required`).'),
                    '404' => new Model\Response(
                        description: 'No live session of the calling admin has this id.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::PROBLEM_REF],
                                'example' => AdminAuthResponses::SESSION_NOT_FOUND,
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSessionCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'client_type' => ['type' => 'String'],
            ],
            description: "The signed-in admin's active sessions (cursor pagination).",
        ),
        new Mutation(
            name: 'delete',
            input: AdminSessionRevokeInput::class,
            processor: AdminSessionProcessor::class,
            read: false,
            description: 'Revoke one of the signed-in admin\'s sessions. Becomes deleteAdminSession.',
        ),
    ],
)]
class AdminSession extends AdminSessionResource {}
