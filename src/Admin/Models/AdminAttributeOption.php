<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminAttributeOptionInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAttributeOptionProcessor;
use Webkul\BagistoApi\Admin\State\AdminAttributeOptionProvider;

/**
 * Sub-resource for attribute options.
 *
 * REST:
 *   POST   /api/admin/catalog/attributes/{attributeId}/options
 *   PUT    /api/admin/catalog/attributes/{attributeId}/options/{optionId}
 *   DELETE /api/admin/catalog/attributes/{attributeId}/options/{optionId}
 *
 * GraphQL:
 *   createAdminCatalogAttributeOption
 *   updateAdminCatalogAttributeOption
 *   deleteAdminCatalogAttributeOption
 *
 * Success response shape: same full attribute detail as GET /api/admin/catalog/attributes/{id}
 * (AdminAttribute DTO via AdminAttributeItemProvider::mapToDtoPublic).
 *
 * Delete returns: { "message": "..." }
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAttributeOption',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/attributes/{attributeId}/options',
            input: AdminAttributeOptionInput::class,
            output: AdminAttribute::class,
            processor: AdminAttributeOptionProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attributes'],
                summary: 'Add an option to a select/multiselect/checkbox attribute',
                description: 'Creates a new option for the given attribute. Only allowed when the attribute type is `select`, `multiselect`, or `checkbox`.',
                parameters: [
                    new Model\Parameter('attributeId', 'path', 'Attribute ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['admin_name'],
                                'properties' => [
                                    'admin_name'   => ['type' => 'string', 'example' => 'Wool'],
                                    'sort_order'   => ['type' => 'integer', 'example' => 2],
                                    'swatch_value' => ['type' => 'string', 'nullable' => true, 'example' => null],
                                    'translations' => [
                                        'type'    => 'object',
                                        'example' => ['en' => ['label' => 'Wool'], 'fr' => ['label' => 'Laine']],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Option created. Returns the full attribute detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'       => 12,
                                    'code'     => 'material',
                                    'type'     => 'select',
                                    'options'  => [
                                        ['id' => 45, 'adminName' => 'Wool', 'sortOrder' => 2],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Attribute type does not support options.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/422', 'status' => 422, 'detail' => 'This attribute type does not support options.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/catalog/attributes/{attributeId}/options/{optionId}',
            input: AdminAttributeOptionInput::class,
            output: AdminAttribute::class,
            provider: AdminAttributeOptionProvider::class,
            processor: AdminAttributeOptionProcessor::class,
            requirements: ['attributeId' => '\d+', 'optionId' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attributes'],
                summary: 'Update an attribute option',
                description: 'Partially updates the given option. Only supplied fields are changed; translations merge per-locale.',
                parameters: [
                    new Model\Parameter('attributeId', 'path', 'Attribute ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                    new Model\Parameter('optionId', 'path', 'Option ID.', true, schema: ['type' => 'integer', 'example' => 45]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'admin_name'   => ['type' => 'string', 'example' => 'Merino Wool'],
                                    'sort_order'   => ['type' => 'integer', 'example' => 1],
                                    'swatch_value' => ['type' => 'string', 'nullable' => true, 'example' => null],
                                    'translations' => [
                                        'type'    => 'object',
                                        'example' => ['en' => ['label' => 'Merino Wool'], 'fr' => ['label' => 'Laine Mérinos']],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Option updated. Returns the full attribute detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 12, 'code' => 'material', 'type' => 'select'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(
                        description: 'Attribute or option not found.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/404', 'status' => 404, 'detail' => 'Attribute option not found.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/catalog/attributes/{attributeId}/options/{optionId}',
            provider: AdminAttributeOptionProvider::class,
            processor: AdminAttributeOptionProcessor::class,
            requirements: ['attributeId' => '\d+', 'optionId' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attributes'],
                summary: 'Delete an attribute option',
                description: 'Deletes the given option. Returns HTTP 409 if the option is referenced by any product attribute values.',
                parameters: [
                    new Model\Parameter('attributeId', 'path', 'Attribute ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                    new Model\Parameter('optionId', 'path', 'Option ID.', true, schema: ['type' => 'integer', 'example' => 45]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Option deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Attribute option deleted successfully.'],
                            ],
                        ]),
                    ),
                    '409' => new Model\Response(
                        description: 'Option is in use by products.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/409', 'status' => 409, 'detail' => 'This option is used by 3 product(s) and cannot be deleted.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'create',
            input: AdminAttributeOptionInput::class,
            processor: AdminAttributeOptionProcessor::class,
            description: 'Add an option to a select/multiselect/checkbox attribute. Becomes createAdminAttributeOption in GraphQL. Returns the created AdminAttributeOption.',
            extraArgs: [
                'attributeId' => ['type' => 'Int!'],
            ],
        ),
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'update',
            input: AdminAttributeOptionInput::class,
            processor: AdminAttributeOptionProcessor::class,
            description: 'Update an attribute option. Becomes updateAdminAttributeOption in GraphQL. Returns the updated AdminAttributeOption.',
            extraArgs: [
                'attributeId' => ['type' => 'Int!'],
                'optionId'    => ['type' => 'Int!'],
            ],
        ),
        new \ApiPlatform\Metadata\GraphQl\Mutation(
            name: 'delete',
            input: AdminAttributeOptionInput::class,
            processor: AdminAttributeOptionProcessor::class,
            description: 'Delete an attribute option. Becomes deleteAdminAttributeOption in GraphQL.',
            extraArgs: [
                'attributeId' => ['type' => 'Int!'],
                'optionId'    => ['type' => 'Int!'],
            ],
        ),
    ],
)]
class AdminAttributeOption
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $attribute_id = null;

    #[ApiProperty(writable: false)]
    public ?string $admin_name = null;

    #[ApiProperty(writable: false)]
    public ?int $sort_order = null;

    #[ApiProperty(writable: false)]
    public ?string $swatch_value = null;
}
