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
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGroupCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerGroupUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupProcessor;
use Webkul\BagistoApi\Admin\State\AdminCustomerGroupWriteProvider;

/**
 * Admin Customer Groups — full CRUD (Block C C2).
 *
 * REST:
 *   GET    /api/admin/customers/groups
 *   GET    /api/admin/customers/groups/{id}
 *   POST   /api/admin/customers/groups
 *   PUT    /api/admin/customers/groups/{id}
 *   DELETE /api/admin/customers/groups/{id}
 *
 * GraphQL: adminCustomerGroups, adminCustomerGroup,
 *          createAdminCustomerGroup, updateAdminCustomerGroup, deleteAdminCustomerGroup
 *
 * Mirrors Webkul\Admin\Http\Controllers\Customers\CustomerGroupController.
 *
 * System groups (is_user_defined = 0) cannot be deleted or have code/is_user_defined changed.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCustomerGroup',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customers/groups',
            input: AdminCustomerGroupCreateInput::class,
            processor: AdminCustomerGroupProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Customer Groups'],
                summary: 'Create a new customer group',
                description: 'New groups are always created with is_user_defined=1 (system groups cannot be created via API).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['code', 'name'],
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'wholesale'],
                                    'name' => ['type' => 'string', 'example' => 'Wholesale'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Customer group created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/customers/groups/{id}',
            input: AdminCustomerGroupUpdateInput::class,
            provider: AdminCustomerGroupWriteProvider::class,
            processor: AdminCustomerGroupProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customer Groups'],
                summary: 'Update a customer group',
                description: 'System groups refuse code/is_user_defined changes (422). Only name can be updated for system groups.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Customer group ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/customers/groups/{id}',
            provider: AdminCustomerGroupWriteProvider::class,
            processor: AdminCustomerGroupProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Customer Groups'],
                summary: 'Delete a customer group',
                description: 'Refuses if the group is a system group (is_user_defined=0) or has customers attached (400).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Customer group ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Customer group deleted.'),
                    '400' => new Model\Response(description: 'Refused — system group or has attached customers.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/customers/groups/{id}',
            provider: AdminCustomerGroupItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Customer Groups'],
                summary: 'Customer group detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Customer group ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/customers/groups',
            provider: AdminCustomerGroupCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Customer Groups'],
                summary: 'List customer groups',
                description: 'Paginated, filterable, sortable list. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('code', 'query', 'Filter by code (partial).', false, schema: ['type' => 'string']),
                    new Model\Parameter('name', 'query', 'Filter by name (partial).', false, schema: ['type' => 'string']),
                    new Model\Parameter('is_user_defined', 'query', 'Filter by system/user-defined flag (0/1).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'code', 'name']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminCustomerGroupCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'code'            => ['type' => 'String'],
                'name'            => ['type' => 'String'],
                'is_user_defined' => ['type' => 'Int'],
                'sort'            => ['type' => 'String'],
                'order'           => ['type' => 'String'],
            ],
            description: 'Admin customer groups listing (cursor pagination).',
        ),
        new Query(
            provider: AdminCustomerGroupItemProvider::class,
            description: 'Admin customer group detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminCustomerGroupCreateInput::class,
            processor: AdminCustomerGroupProcessor::class,
            description: 'Create a new customer group. Becomes createAdminCustomerGroup.',
        ),
        new Mutation(
            name: 'update',
            input: AdminCustomerGroupUpdateInput::class,
            processor: AdminCustomerGroupProcessor::class,
            description: 'Update a customer group. Becomes updateAdminCustomerGroup.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCustomerGroupUpdateInput::class,
            processor: AdminCustomerGroupProcessor::class,
            description: 'Delete a customer group. Becomes deleteAdminCustomerGroup.',
        ),
    ],
)]
class AdminCustomerGroup
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $code = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?int $is_user_defined = null;

    /** Detail-only — null on listing rows */
    #[ApiProperty(writable: false)]
    public ?int $customers_count = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
