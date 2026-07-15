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
use Webkul\BagistoApi\Admin\Dto\AdminAttributeFamilyCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminAttributeFamilyUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyItemProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyProcessor;
use Webkul\BagistoApi\Admin\State\AdminAttributeFamilyWriteProvider;

/**
 * Admin Catalog → Attribute Families endpoints.
 *
 * Phase 1.5 (read):
 *   GET    /api/admin/catalog/families          datagrid-parity listing
 *   GET    /api/admin/catalog/families/{id}     detail with attribute groups
 *
 * Phase 4 (CRUD):
 *   POST   /api/admin/catalog/families          create
 *   PUT    /api/admin/catalog/families/{id}     update
 *   DELETE /api/admin/catalog/families/{id}     delete
 *
 * GraphQL mutations (Phase 4): createAdminAttributeFamily / updateAdminAttributeFamily / deleteAdminAttributeFamily.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAttributeFamily',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/families',
            input: AdminAttributeFamilyCreateInput::class,
            processor: AdminAttributeFamilyProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attribute Families'],
                summary: 'Create a new attribute family',
                description: 'Creates an attribute family with optional nested attribute groups and per-group custom_attributes. `code` must be unique and pass the Code rule.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'name'],
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'electronics'],
                                    'name' => ['type' => 'string', 'example' => 'Electronics'],
                                    'attribute_groups' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'object'],
                                        'example' => [
                                            [
                                                'code' => 'general',
                                                'name' => 'General',
                                                'column' => 1,
                                                'position' => 1,
                                                'custom_attributes' => [
                                                    ['id' => 1],
                                                    ['id' => 2],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Family created. Returns the same shape as GET /catalog/families/{id}.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 4,
                                    'code' => 'electronics',
                                    'name' => 'Electronics',
                                    'attributeGroups' => [
                                        ['id' => 11, 'code' => 'general', 'name' => 'General', 'column' => 1, 'position' => 1, 'attributes' => []],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Validation failure.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/422', 'status' => 422, 'detail' => 'The code field is required.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/catalog/families/{id}',
            input: AdminAttributeFamilyUpdateInput::class,
            provider: AdminAttributeFamilyWriteProvider::class,
            processor: AdminAttributeFamilyProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attribute Families'],
                summary: 'Update an attribute family',
                description: 'Updates an attribute family. Inside `attribute_groups`, items keyed by numeric id update existing groups; items keyed by `group_*` create new groups; omitted existing ids are deleted.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Family ID.', true, schema: ['type' => 'integer', 'example' => 4]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'name'],
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'electronics'],
                                    'name' => ['type' => 'string', 'example' => 'Electronics (updated)'],
                                    'attribute_groups' => [
                                        'type' => 'object',
                                        'example' => [
                                            '11' => [
                                                'code' => 'general',
                                                'name' => 'General',
                                                'column' => 1,
                                                'position' => 1,
                                                'custom_attributes' => [
                                                    ['id' => 1, 'position' => 1],
                                                ],
                                            ],
                                            'group_new_1' => [
                                                'code' => 'pricing',
                                                'name' => 'Pricing',
                                                'column' => 2,
                                                'position' => 2,
                                                'custom_attributes' => [
                                                    ['id' => 11, 'position' => 1],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Family updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 4, 'code' => 'electronics', 'name' => 'Electronics (updated)'],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Validation failure.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/422', 'status' => 422, 'detail' => 'The code has already been taken.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/catalog/families/{id}',
            provider: AdminAttributeFamilyWriteProvider::class,
            processor: AdminAttributeFamilyProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attribute Families'],
                summary: 'Delete an attribute family',
                description: 'Refuses if this is the last family (HTTP 400) or if any product is using it (HTTP 400).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Family ID.', true, schema: ['type' => 'integer', 'example' => 4]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Family deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Attribute family deleted successfully.'],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(
                        description: 'Cannot delete — last family or products attached.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/400', 'status' => 400, 'detail' => 'At least one attribute family is required.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(
                        description: 'Family not found.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/404', 'status' => 404, 'detail' => 'Attribute family not found.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/catalog/families/{id}',
            provider: AdminAttributeFamilyItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attribute Families'],
                summary: 'Attribute family detail with attribute groups and attributes',
                description: 'Returns one attribute family with all attribute groups and their associated attributes (including pivot position).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Attribute family ID.', true, schema: ['type' => 'integer', 'example' => 1]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Attribute family detail with groups and attributes.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'code' => 'default',
                                    'name' => 'Default',
                                    'attributeGroups' => [
                                        [
                                            'id' => 1,
                                            'code' => 'general',
                                            'name' => 'General',
                                            'column' => 1,
                                            'position' => 1,
                                            'attributes' => [
                                                ['id' => 1, 'code' => 'sku', 'type' => 'text', 'isRequired' => 1, 'column' => 1, 'position' => 1],
                                                ['id' => 2, 'code' => 'name', 'type' => 'text', 'isRequired' => 1, 'column' => 1, 'position' => 2],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(
                        description: 'Attribute family not found.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'type' => '/errors/404',
                                    'title' => 'An error occurred',
                                    'status' => 404,
                                    'detail' => 'Attribute family not found.',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/catalog/families',
            provider: AdminAttributeFamilyCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attribute Families'],
                summary: 'List attribute families (datagrid parity)',
                description: 'Paginated, filterable, sortable attribute family list mirroring the admin Catalog → Families datagrid.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('id', 'query', 'Filter by family ID — single integer or comma-separated list.', false, schema: ['type' => 'string', 'example' => '1']),
                    new Model\Parameter('code', 'query', 'Partial family code match (SQL LIKE).', false, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('name', 'query', 'Partial family name match (SQL LIKE).', false, schema: ['type' => 'string', 'example' => 'Default']),
                    new Model\Parameter('sort', 'query', 'Column to sort by.', false, schema: ['type' => 'string', 'enum' => ['id', 'code', 'name'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of attribute family rows in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        ['id' => 1, 'code' => 'default',  'name' => 'Default'],
                                        ['id' => 3, 'code' => 'apparel',  'name' => 'Apparel'],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 1,
                                        'total' => 2,
                                        'from' => 1,
                                        'to' => 2,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminAttributeFamilyCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'id' => ['type' => 'String'],
                'code' => ['type' => 'String'],
                'name' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin catalog attribute families listing (cursor pagination). Mirrors GET /api/admin/catalog/families.',
        ),
        new Query(
            provider: AdminAttributeFamilyItemProvider::class,
            description: 'Admin catalog attribute family detail by id, with attribute groups and attributes inlined.',
        ),
        new Mutation(
            name: 'create',
            input: AdminAttributeFamilyCreateInput::class,
            processor: AdminAttributeFamilyProcessor::class,
            description: 'Create a new attribute family. Becomes createAdminAttributeFamily.',
        ),
        new Mutation(
            name: 'update',
            input: AdminAttributeFamilyUpdateInput::class,
            processor: AdminAttributeFamilyProcessor::class,
            description: 'Update an attribute family. Becomes updateAdminAttributeFamily.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminAttributeFamilyUpdateInput::class,
            processor: AdminAttributeFamilyProcessor::class,
            description: 'Delete an attribute family. Becomes deleteAdminAttributeFamily.',
        ),
    ],
)]
class AdminAttributeFamily
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $code = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    /**
     * Detail-only: attribute groups with their attributes. Null in listing rows.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $attribute_groups = null;
}
