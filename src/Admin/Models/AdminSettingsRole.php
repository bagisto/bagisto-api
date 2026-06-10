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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsRoleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsRoleUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminSettingsRoleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsRoleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsRoleProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsRoleWriteProvider;

/**
 * Admin Settings → Roles endpoints (Block B Wave 2).
 *
 * REST:
 *   GET    /api/admin/settings/roles
 *   GET    /api/admin/settings/roles/{id}
 *   POST   /api/admin/settings/roles
 *   PUT    /api/admin/settings/roles/{id}
 *   DELETE /api/admin/settings/roles/{id}    (guards: in-use, last-role)
 *
 * GraphQL:
 *   adminSettingsRoles / adminSettingsRole
 *   createAdminSettingsRole / updateAdminSettingsRole / deleteAdminSettingsRole
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\RoleController 1:1.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsRole',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/roles',
            input: AdminSettingsRoleCreateInput::class,
            processor: AdminSettingsRoleProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Roles'],
                summary: 'Create a new role',
                description: 'Validates name, description, permission_type (all|custom). When permission_type=custom, permissions array is required.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'description', 'permission_type'],
                                'properties' => [
                                    'name'            => ['type' => 'string', 'example' => 'Catalog Manager'],
                                    'description'     => ['type' => 'string', 'example' => 'Can manage catalog only'],
                                    'permission_type' => ['type' => 'string', 'enum' => ['all', 'custom'], 'example' => 'custom'],
                                    'permissions'     => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['catalog.products', 'catalog.categories']],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Role created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/roles/{id}',
            input: AdminSettingsRoleUpdateInput::class,
            provider: AdminSettingsRoleWriteProvider::class,
            processor: AdminSettingsRoleProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Roles'],
                summary: 'Update a role',
                parameters: [
                    new Model\Parameter('id', 'path', 'Role ID.', true, schema: ['type' => 'integer', 'example' => 2]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'description', 'permission_type'],
                                'properties' => [
                                    'name'            => ['type' => 'string', 'example' => 'Catalog Manager'],
                                    'description'     => ['type' => 'string', 'example' => 'Updated description'],
                                    'permission_type' => ['type' => 'string', 'enum' => ['all', 'custom'], 'example' => 'custom'],
                                    'permissions'     => ['type' => 'array', 'items' => ['type' => 'string'], 'example' => ['catalog.products']],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Role updated.'),
                    '404' => new Model\Response(description: 'Role not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/roles/{id}',
            provider: AdminSettingsRoleWriteProvider::class,
            processor: AdminSettingsRoleProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Roles'],
                summary: 'Delete a role',
                description: 'Refuses with HTTP 400 when the role is assigned to any admin (admins.role_id), or when it is the only remaining role.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Role ID.', true, schema: ['type' => 'integer', 'example' => 2]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Role deleted.'),
                    '400' => new Model\Response(description: 'In use or last remaining role.'),
                    '404' => new Model\Response(description: 'Role not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/roles/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsRoleItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Roles'],
                summary: 'Role detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Role ID.', true, schema: ['type' => 'integer', 'example' => 2]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Single role.'),
                    '404' => new Model\Response(description: 'Role not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/roles',
            provider: AdminSettingsRoleCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Roles'],
                summary: 'List roles',
                description: 'Datagrid-parity listing. Filters: name (LIKE), permission_type. Sort: id (default desc), name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('permission_type', 'query', 'Filter by permission_type.', false, schema: ['type' => 'string', 'enum' => ['all', 'custom']]),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'name'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in { data, meta } envelope.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsRoleCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'name'            => ['type' => 'String'],
                'permission_type' => ['type' => 'String'],
                'sort'            => ['type' => 'String'],
                'order'           => ['type' => 'String'],
            ],
            description: 'Admin roles listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsRoleItemProvider::class,
            description: 'Admin role detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsRoleCreateInput::class,
            processor: AdminSettingsRoleProcessor::class,
            description: 'Create a role. Becomes createAdminSettingsRole.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsRoleUpdateInput::class,
            processor: AdminSettingsRoleProcessor::class,
            description: 'Update a role. Becomes updateAdminSettingsRole.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsRoleUpdateInput::class,
            processor: AdminSettingsRoleProcessor::class,
            description: 'Delete a role. Becomes deleteAdminSettingsRole. Refused for in-use or last role.',
        ),
    ],
)]
class AdminSettingsRole
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false, example: 1)]
    public ?int $id = null;

    #[ApiProperty(writable: false, example: 'Administrator')]
    public ?string $name = null;

    #[ApiProperty(writable: false, example: 'Full access to all modules')]
    public ?string $description = null;

    #[ApiProperty(writable: false, example: 'all')]
    public ?string $permission_type = null;

    /** @var array<int, string>|null */
    #[ApiProperty(writable: false, example: ['catalog.products.create', 'sales.orders.view'])]
    public ?array $permissions = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:15:00+00:00')]
    public ?string $created_at = null;

    #[ApiProperty(writable: false, example: '2026-05-25T08:20:00+00:00')]
    public ?string $updated_at = null;
}
