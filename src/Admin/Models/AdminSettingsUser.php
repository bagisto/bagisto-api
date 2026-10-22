<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminSessionRevokeInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsUserCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsUserUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserRevokeSessionsProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsUserWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsUser',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/users',
            input: AdminSettingsUserCreateInput::class,
            processor: AdminSettingsUserProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Users'],
                summary: 'Create a new admin user',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'email', 'password', 'role_id'],

                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'example' => 'Jane Doe',
                                    ],

                                    'email' => [
                                        'type' => 'string',
                                        'example' => 'jane@example.com',
                                    ],

                                    'password' => [
                                        'type' => 'string',
                                        'example' => 'secret123',
                                    ],

                                    'role_id' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                    ],

                                    'status' => [
                                        'type' => 'integer',
                                        'enum' => [0, 1],
                                        'example' => 1,
                                    ],

                                    'image' => [
                                        'type' => 'string',
                                        'nullable' => true,
                                        'example' => 'admins/jane.png',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Admin created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/users/{id}',
            input: AdminSettingsUserUpdateInput::class,
            provider: AdminSettingsUserWriteProvider::class,
            processor: AdminSettingsUserProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Users'],
                summary: 'Update an admin user',
                description: 'Partial update: `name`, `email`, `role_id` and `status` keep their stored value when left out, `password` changes only when sent (at least 6 characters), and `image` sets or, with null, clears the stored image path. Needs `settings.users.edit`. Deactivating the user ends their API login sessions; a password change does so when *Admin Authentication* is set to.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Admin ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 5,
                    ]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',

                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'example' => 'Jane Manager',
                                    ],

                                    'email' => [
                                        'type' => 'string',
                                        'example' => 'jane.manager@example.com',
                                    ],

                                    'password' => [
                                        'type' => 'string',
                                        'nullable' => true,
                                        'example' => 'N3wSecret!',
                                    ],

                                    'role_id' => [
                                        'type' => 'integer',
                                        'example' => 2,
                                    ],

                                    'status' => [
                                        'type' => 'integer',
                                        'enum' => [0, 1],
                                        'example' => 1,
                                    ],

                                    'image' => [
                                        'type' => 'string',
                                        'nullable' => true,
                                        'example' => null,
                                    ],
                                ],
                            ],

                            'example' => [
                                'name' => 'Jane Manager',
                                'email' => 'jane.manager@example.com',
                                'role_id' => 2,
                                'status' => 1,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Admin updated.'),
                    '400' => new Model\Response(description: 'Deactivating the last active all-access admin, or changing the role while only one admin has all access.'),
                    '403' => new Model\Response(description: 'Admin role lacks settings.users.edit, or the user\'s current or new role holds a permission the caller lacks.'),
                    '404' => new Model\Response(description: 'Admin not found.'),
                    '422' => new Model\Response(description: 'Validation failure (email taken, unknown role_id, password under 6 characters).'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/users/{id}',
            provider: AdminSettingsUserWriteProvider::class,
            processor: AdminSettingsUserProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Users'],
                summary: 'Delete an admin user',
                description: 'Refuses if the caller is deleting themselves or if this is the last admin.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Admin ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 5,
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Admin deleted.'),
                    '400' => new Model\Response(description: 'Refused — self-delete or last admin.'),
                    '404' => new Model\Response(description: 'Admin not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/settings/users/{id}/revoke-sessions',
            processor: AdminSettingsUserRevokeSessionsProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            read: false,
            deserialize: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Users'],
                summary: 'Revoke every session of an admin user',
                description: 'Revokes every live login session of the admin user, so their access and refresh tokens stop working at once. API tokens are not affected. Requires `settings.api_sessions.delete`.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Admin ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 5,
                    ]),
                ],
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Sessions revoked; `message` says how many.'),
                    '403' => new Model\Response(description: 'The role lacks `settings.api_sessions.delete`.'),
                    '404' => new Model\Response(description: 'Admin not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/users/{id}',
            provider: AdminSettingsUserItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Users'],
                summary: 'Admin user detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Admin ID.', true, schema: [
                        'type' => 'integer',
                        'example' => 5,
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Single admin row.'),
                    '404' => new Model\Response(description: 'Admin not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/users',
            provider: AdminSettingsUserCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Users'],
                summary: 'List admin users',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page (1-based).', false, schema: [
                        'type' => 'integer',
                        'example' => 1,
                    ]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: [
                        'type' => 'integer',
                        'example' => 10,
                    ]),
                    new Model\Parameter('name', 'query', 'Filter by name (partial match).', false, schema: ['type' => 'string']),
                    new Model\Parameter('email', 'query', 'Filter by email (partial match).', false, schema: ['type' => 'string']),
                    new Model\Parameter('role_id', 'query', 'Filter by role ID.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('status', 'query', 'Filter by status (0/1).', false, schema: [
                        'type' => 'integer',
                        'enum' => [0, 1],
                    ]),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: [
                        'type' => 'string',
                        'enum' => ['id', 'name', 'email'],
                        'example' => 'id',
                    ]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: [
                        'type' => 'string',
                        'enum' => ['asc', 'desc'],
                        'example' => 'desc',
                    ]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in { data, meta } envelope.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsUserCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name' => ['type' => 'String'],
                'email' => ['type' => 'String'],
                'role_id' => ['type' => 'Int'],
                'status' => ['type' => 'Int'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin settings users listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsUserItemProvider::class,
            description: 'Admin settings user detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsUserCreateInput::class,
            processor: AdminSettingsUserProcessor::class,
            description: 'Create a new admin user. Becomes createAdminSettingsUser.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsUserUpdateInput::class,
            processor: AdminSettingsUserProcessor::class,
            description: 'Update an admin user. Becomes updateAdminSettingsUser.',
        ),
        new Mutation(
            name: 'revokeSessions',
            input: AdminSessionRevokeInput::class,
            processor: AdminSettingsUserRevokeSessionsProcessor::class,
            read: false,
            deserialize: false,
            write: true,
            description: 'Revoke every login session of an admin user. Becomes revokeSessionsAdminSettingsUser; requires settings.api_sessions.delete.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsUserUpdateInput::class,
            processor: AdminSettingsUserProcessor::class,
            description: 'Delete an admin user. Becomes deleteAdminSettingsUser.',
        ),
    ],
)]
class AdminSettingsUser
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false, example: 1)]
    public ?int $id = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false, example: 'John Doe')]
    public ?string $name = null;

    /**
     * The email.
     */
    #[ApiProperty(writable: false, example: 'john@example.com')]
    public ?string $email = null;

    /**
     * The role id.
     */
    #[ApiProperty(writable: false, example: 1)]
    public ?int $role_id = null;

    /**
     * The role name.
     */
    #[ApiProperty(writable: false, example: 'Administrator')]
    public ?string $role_name = null;

    /**
     * The status.
     */
    #[ApiProperty(writable: false, example: 1)]
    public ?int $status = null;

    /**
     * The image.
     */
    #[ApiProperty(writable: false, example: 'admins/1/avatar.png')]
    public ?string $image = null;

    /**
     * The image url.
     */
    #[ApiProperty(writable: false, example: 'https://your-domain.com/storage/admins/1/avatar.png')]
    public ?string $image_url = null;

    /**
     * The created at.
     */
    #[ApiProperty(writable: false, example: '2026-05-25T08:15:00+00:00')]
    public ?string $created_at = null;

    /**
     * The updated at.
     */
    #[ApiProperty(writable: false, example: '2026-05-25T08:20:00+00:00')]
    public ?string $updated_at = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false, example: 'User deleted successfully.')]
    public ?string $message = null;
}
