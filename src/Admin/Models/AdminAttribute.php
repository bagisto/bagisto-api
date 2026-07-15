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
use Webkul\BagistoApi\Admin\Dto\AdminAttributeCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminAttributeUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAttributeCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeItemProvider;
use Webkul\BagistoApi\Admin\State\AdminAttributeProcessor;

/**
 * Admin Catalog → Attributes endpoints.
 *
 * REST (Phase 1.4 — read):
 *   GET /api/admin/catalog/attributes          datagrid-parity listing
 *   GET /api/admin/catalog/attributes/{id}     detail with translations + options
 *
 * REST (Phase 3 — write):
 *   POST   /api/admin/catalog/attributes       create
 *   PUT    /api/admin/catalog/attributes/{id}  update
 *   DELETE /api/admin/catalog/attributes/{id}  delete
 *
 * GraphQL mutations added in Phase 3:
 *   createAdminCatalogAttribute
 *   updateAdminCatalogAttribute
 *   deleteAdminCatalogAttribute
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAttribute',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/attributes',
            input: AdminAttributeCreateInput::class,
            processor: AdminAttributeProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attributes'],
                summary: 'Create a new attribute',
                description: 'Creates an attribute with optional translations and options (for select/multiselect/checkbox types). The `code` must be unique, pass the Code rule (letters/digits/underscore), and not be a reserved word (`type`, `attribute_family_id`).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'admin_name', 'type'],
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'material'],
                                    'admin_name' => ['type' => 'string', 'example' => 'Material'],
                                    'type' => ['type' => 'string', 'example' => 'select', 'enum' => ['text', 'textarea', 'price', 'boolean', 'select', 'multiselect', 'checkbox', 'date', 'datetime', 'image', 'file']],
                                    'swatch_type' => ['type' => 'string', 'nullable' => true, 'example' => 'text'],
                                    'is_required' => ['type' => 'boolean', 'example' => false],
                                    'is_unique' => ['type' => 'boolean', 'example' => false],
                                    'is_filterable' => ['type' => 'boolean', 'example' => true],
                                    'is_configurable' => ['type' => 'boolean', 'example' => false],
                                    'is_visible_on_front' => ['type' => 'boolean', 'example' => true],
                                    'is_comparable' => ['type' => 'boolean', 'example' => false],
                                    'value_per_locale' => ['type' => 'boolean', 'example' => false],
                                    'value_per_channel' => ['type' => 'boolean', 'example' => false],
                                    'enable_wysiwyg' => ['type' => 'boolean', 'example' => false],
                                    'validation' => ['type' => 'string', 'nullable' => true, 'example' => null],
                                    'default_value' => ['type' => 'string', 'nullable' => true, 'example' => null],
                                    'position' => ['type' => 'integer', 'example' => 10],
                                    'translations' => ['type' => 'object', 'example' => ['en' => ['name' => 'Material'], 'fr' => ['name' => 'Matière']]],
                                    'options' => ['type' => 'array', 'items' => ['type' => 'object'], 'example' => [['admin_name' => 'Cotton', 'sort_order' => 1, 'translations' => ['en' => ['label' => 'Cotton'], 'fr' => ['label' => 'Coton']]]]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Attribute created successfully. Returns the full attribute detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 50,
                                    'code' => 'material',
                                    'type' => 'select',
                                    'adminName' => 'Material',
                                    'isRequired' => 0,
                                    'isUnique' => 0,
                                    'valuePerLocale' => 0,
                                    'valuePerChannel' => 0,
                                    'isFilterable' => 1,
                                    'isConfigurable' => 0,
                                    'isVisibleOnFront' => 1,
                                    'isUserDefined' => 1,
                                    'swatchType' => 'text',
                                    'position' => 10,
                                    'locale' => 'en',
                                    'createdAt' => '2026-05-22T10:00:00+00:00',
                                    'updatedAt' => '2026-05-22T10:00:00+00:00',
                                    'validation' => null,
                                    'defaultValue' => null,
                                    'isComparable' => 0,
                                    'enableWysiwyg' => 0,
                                    'regex' => null,
                                    'translations' => [
                                        ['locale' => 'en', 'name' => 'Material'],
                                        ['locale' => 'fr', 'name' => 'Matière'],
                                    ],
                                    'options' => [
                                        ['id' => 101, 'adminName' => 'Cotton', 'sortOrder' => 1, 'swatchValue' => null, 'swatchValueUrl' => null, 'translations' => [['locale' => 'en', 'label' => 'Cotton'], ['locale' => 'fr', 'label' => 'Coton']]],
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
            uriTemplate: '/catalog/attributes/{id}',
            input: AdminAttributeUpdateInput::class,
            provider: AdminAttributeItemProvider::class,
            processor: AdminAttributeProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attributes'],
                summary: 'Update an attribute',
                description: 'Updates an attribute. The `code` field cannot be changed (returns 422 if a different code is supplied). Changing `type` is refused when product attribute values exist.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Attribute ID.', true, schema: ['type' => 'integer', 'example' => 50]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'admin_name', 'type'],
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'material'],
                                    'admin_name' => ['type' => 'string', 'example' => 'Material (updated)'],
                                    'type' => ['type' => 'string', 'example' => 'select'],
                                    'is_filterable' => ['type' => 'boolean', 'example' => true],
                                    'translations' => ['type' => 'object', 'example' => ['en' => ['name' => 'Material (updated)'], 'fr' => ['name' => 'Matière (mis à jour)']]],
                                    'options' => ['type' => 'array', 'items' => ['type' => 'object'], 'description' => 'Full replacement list. Items with id are updated; without id are inserted; omitted ids are deleted.'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Attribute updated. Returns the full attribute detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 50, 'code' => 'material', 'adminName' => 'Material (updated)', 'type' => 'select'],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Code change refused or type immutable.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/422', 'status' => 422, 'detail' => 'Attribute code cannot be changed.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/catalog/attributes/{id}',
            provider: AdminAttributeItemProvider::class,
            processor: AdminAttributeProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attributes'],
                summary: 'Delete an attribute',
                description: 'Deletes a user-defined attribute. Returns HTTP 403 for system attributes, HTTP 409 if the attribute is used in attribute families.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Attribute ID.', true, schema: ['type' => 'integer', 'example' => 50]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Attribute deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Attribute deleted successfully.'],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(
                        description: 'System attribute — cannot delete.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/403', 'status' => 403, 'detail' => 'System attributes cannot be deleted.'],
                            ],
                        ]),
                    ),
                    '409' => new Model\Response(
                        description: 'Attribute is part of one or more attribute families.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/409', 'status' => 409, 'detail' => 'Attribute is part of one or more attribute families. Remove it from those families first.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/catalog/attributes/{id}',
            provider: AdminAttributeItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attributes'],
                summary: 'Attribute detail with translations and options',
                description: 'Returns one attribute with all locale translations and all options (each option also includes its own translations). For `select`, `multiselect`, and `checkbox` types, the `options` array is fully populated; for all other types it is `null`.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Attribute ID.', true, schema: ['type' => 'integer', 'example' => 12]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Attribute detail with all translations and options.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 12,
                                    'code' => 'color',
                                    'type' => 'select',
                                    'adminName' => 'Color',
                                    'isRequired' => 0,
                                    'isUnique' => 0,
                                    'valuePerLocale' => 0,
                                    'valuePerChannel' => 0,
                                    'isFilterable' => 1,
                                    'isConfigurable' => 1,
                                    'isVisibleOnFront' => 1,
                                    'isUserDefined' => 1,
                                    'swatchType' => 'color',
                                    'position' => 5,
                                    'locale' => 'en',
                                    'createdAt' => '2026-01-12T08:15:00+00:00',
                                    'updatedAt' => '2026-04-30T14:20:09+00:00',
                                    'validation' => null,
                                    'defaultValue' => null,
                                    'isComparable' => 0,
                                    'enableWysiwyg' => 0,
                                    'regex' => null,
                                    'translations' => [
                                        ['locale' => 'en', 'name' => 'Color'],
                                        ['locale' => 'fr', 'name' => 'Couleur'],
                                    ],
                                    'options' => [
                                        [
                                            'id' => 33,
                                            'adminName' => 'Red',
                                            'sortOrder' => 1,
                                            'swatchValue' => '#FF0000',
                                            'swatchValueUrl' => null,
                                            'translations' => [
                                                ['locale' => 'en', 'label' => 'Red'],
                                                ['locale' => 'fr', 'label' => 'Rouge'],
                                            ],
                                        ],
                                        [
                                            'id' => 34,
                                            'adminName' => 'Blue',
                                            'sortOrder' => 2,
                                            'swatchValue' => '#0000FF',
                                            'swatchValueUrl' => null,
                                            'translations' => [
                                                ['locale' => 'en', 'label' => 'Blue'],
                                                ['locale' => 'fr', 'label' => 'Bleu'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(
                        description: 'Attribute not found.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'type' => '/errors/404',
                                    'title' => 'An error occurred',
                                    'status' => 404,
                                    'detail' => 'Attribute not found',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/catalog/attributes',
            provider: AdminAttributeCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Attributes'],
                summary: 'List attributes (datagrid parity)',
                description: 'Paginated, filterable, sortable attribute list mirroring the admin Catalog → Attributes datagrid.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('id', 'query', 'Filter by attribute ID — single integer or comma-separated list (e.g. "1" or "1,2").', false, schema: ['type' => 'string', 'example' => '1']),
                    new Model\Parameter('code', 'query', 'Partial attribute code match (SQL LIKE).', false, schema: ['type' => 'string', 'example' => 'color']),
                    new Model\Parameter('type', 'query', 'Exact attribute type filter.', false, schema: ['type' => 'string', 'enum' => ['text', 'textarea', 'price', 'boolean', 'select', 'multiselect', 'datetime', 'date', 'image', 'file', 'checkbox'], 'example' => 'select']),
                    new Model\Parameter('admin_name', 'query', 'Partial admin name match (SQL LIKE).', false, schema: ['type' => 'string', 'example' => 'Color']),
                    new Model\Parameter('is_required', 'query', 'Filter by is_required (0 or 1).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 1]),
                    new Model\Parameter('is_unique', 'query', 'Filter by is_unique (0 or 1).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 0]),
                    new Model\Parameter('is_filterable', 'query', 'Filter by is_filterable (0 or 1).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 1]),
                    new Model\Parameter('is_configurable', 'query', 'Filter by is_configurable (0 or 1).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 0]),
                    new Model\Parameter('is_visible_on_front', 'query', 'Filter by is_visible_on_front (0 or 1).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 1]),
                    new Model\Parameter('is_user_defined', 'query', 'Filter by is_user_defined (0 or 1).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 1]),
                    new Model\Parameter('value_per_locale', 'query', 'Filter by value_per_locale (0 or 1).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 0]),
                    new Model\Parameter('value_per_channel', 'query', 'Filter by value_per_channel (0 or 1).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 0]),
                    new Model\Parameter('locale', 'query', 'Locale code for translation resolution (e.g. "en").', false, schema: ['type' => 'string', 'example' => 'en']),
                    new Model\Parameter('sort', 'query', 'Column to sort by.', false, schema: ['type' => 'string', 'enum' => ['id', 'code', 'admin_name', 'type', 'position'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of attribute rows in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 1,
                                            'code' => 'sku',
                                            'type' => 'text',
                                            'adminName' => 'SKU',
                                            'isRequired' => 1,
                                            'isUnique' => 1,
                                            'valuePerLocale' => 0,
                                            'valuePerChannel' => 0,
                                            'isFilterable' => 0,
                                            'isConfigurable' => 0,
                                            'isVisibleOnFront' => 0,
                                            'isUserDefined' => 0,
                                            'swatchType' => null,
                                            'position' => 1,
                                            'locale' => 'en',
                                            'createdAt' => '2024-01-01T00:00:00+00:00',
                                            'updatedAt' => '2024-01-01T00:00:00+00:00',
                                            'translations' => null,
                                            'options' => null,
                                            'validation' => null,
                                            'defaultValue' => null,
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 5,
                                        'total' => 47,
                                        'from' => 1,
                                        'to' => 10,
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
            provider: AdminAttributeCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'id' => ['type' => 'String'],
                'code' => ['type' => 'String'],
                'type' => ['type' => 'String'],
                'admin_name' => ['type' => 'String'],
                'is_required' => ['type' => 'Int'],
                'is_unique' => ['type' => 'Int'],
                'is_filterable' => ['type' => 'Int'],
                'is_configurable' => ['type' => 'Int'],
                'is_visible_on_front' => ['type' => 'Int'],
                'is_user_defined' => ['type' => 'Int'],
                'locale' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin catalog attributes listing (cursor pagination). Mirrors GET /api/admin/catalog/attributes.',
        ),
        new Query(
            provider: AdminAttributeItemProvider::class,
            description: 'Admin catalog attribute detail by id, with translations and options inlined.',
        ),
        new Mutation(
            name: 'create',
            input: AdminAttributeCreateInput::class,
            processor: AdminAttributeProcessor::class,
            description: 'Create a new catalog attribute. Becomes createAdminAttribute in GraphQL.',
        ),
        new Mutation(
            name: 'update',
            input: AdminAttributeUpdateInput::class,
            processor: AdminAttributeProcessor::class,
            description: 'Update an existing catalog attribute. Becomes updateAdminAttribute in GraphQL.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminAttributeUpdateInput::class,
            processor: AdminAttributeProcessor::class,
            description: 'Delete a user-defined catalog attribute. Becomes deleteAdminAttribute in GraphQL.',
        ),
    ],
)]
class AdminAttribute
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $code = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $admin_name = null;

    #[ApiProperty(writable: false)]
    public ?int $is_required = null;

    #[ApiProperty(writable: false)]
    public ?int $is_unique = null;

    #[ApiProperty(writable: false)]
    public ?int $value_per_locale = null;

    #[ApiProperty(writable: false)]
    public ?int $value_per_channel = null;

    #[ApiProperty(writable: false)]
    public ?int $is_filterable = null;

    #[ApiProperty(writable: false)]
    public ?int $is_configurable = null;

    #[ApiProperty(writable: false)]
    public ?int $is_visible_on_front = null;

    #[ApiProperty(writable: false)]
    public ?int $is_user_defined = null;

    #[ApiProperty(writable: false)]
    public ?string $swatch_type = null;

    #[ApiProperty(writable: false)]
    public ?int $position = null;

    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    /**
     * Detail-only: all locale translations. Null in listing rows.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    /**
     * Detail-only: attribute options (for select/multiselect/checkbox types). Null in listing rows.
     *
     * @var array<int, mixed>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $options = null;

    /**
     * Detail-only: validation rule string. Null in listing rows.
     */
    #[ApiProperty(writable: false)]
    public ?string $validation = null;

    /**
     * Detail-only: default value. Null in listing rows.
     */
    #[ApiProperty(writable: false)]
    public ?string $default_value = null;

    /**
     * Detail-only: include in product compare. Null in listing rows.
     */
    #[ApiProperty(writable: false)]
    public ?int $is_comparable = null;

    /**
     * Detail-only: WYSIWYG editor enabled (textarea type). Null in listing rows.
     */
    #[ApiProperty(writable: false)]
    public ?int $enable_wysiwyg = null;

    /**
     * Detail-only: custom regex pattern (used when validation=regex). Null in listing rows.
     */
    #[ApiProperty(writable: false)]
    public ?string $regex = null;
}
