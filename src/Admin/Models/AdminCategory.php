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
use Webkul\BagistoApi\Admin\Dto\AdminCategoryCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCategoryUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCategoryCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCategoryItemProvider;
use Webkul\BagistoApi\Admin\State\AdminCategoryProcessor;
use Webkul\BagistoApi\Admin\State\AdminCategoryWriteProvider;

/**
 * Admin Catalog → Categories endpoints.
 *
 * REST    : GET /api/admin/catalog/categories  → datagrid-parity listing
 *           (detail and GraphQL operations added in later Phase 1.3 tasks)
 *
 * Mirrors Webkul\Admin\DataGrids\Catalog\CategoryDataGrid 1:1 — same join
 * (categories × category_translations on the active locale), same filters,
 * same sort columns.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCategory',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/catalog/categories',
            input: AdminCategoryCreateInput::class,
            processor: AdminCategoryProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'Create a new category',
                description: 'Mirrors Bagisto admin Catalog → Categories → Create. Validates slug (unique), name, position, attributes. `description` required when `display_mode` is `description_only` or `products_and_description`. File-upload for `logo_path` / `banner_path` is NOT supported in v1.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['slug', 'name', 'position', 'attributes'],
                                'properties' => [
                                    'slug' => ['type' => 'string', 'example' => 'apparel'],
                                    'name' => ['type' => 'string', 'example' => 'Apparel'],
                                    'description' => ['type' => 'string', 'example' => "Men's and women's apparel"],
                                    'position' => ['type' => 'integer', 'example' => 1],
                                    'attributes' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [11, 23]],
                                    'parent_id' => ['type' => 'integer', 'nullable' => true, 'example' => 1],
                                    'display_mode' => ['type' => 'string', 'enum' => ['products_and_description', 'products_only', 'description_only'], 'example' => 'products_and_description'],
                                    'status' => ['type' => 'integer', 'example' => 1],
                                    'locale' => ['type' => 'string', 'example' => 'en'],
                                    'meta_title' => ['type' => 'string', 'example' => 'Apparel'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Category created. Returns the same shape as GET /catalog/categories/{id}.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 7,
                                    'position' => 1,
                                    'status' => 1,
                                    'parentId' => 1,
                                    'displayMode' => 'products_and_description',
                                    'name' => 'Apparel',
                                    'slug' => 'apparel',
                                    'description' => "Men's and women's apparel",
                                    'locale' => 'en',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Validation failure.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/422', 'status' => 422, 'detail' => 'The slug field is required.'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/catalog/categories/{id}',
            input: AdminCategoryUpdateInput::class,
            provider: AdminCategoryWriteProvider::class,
            processor: AdminCategoryProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'Update a category (and/or move it)',
                description: 'Mirrors Bagisto admin Catalog → Categories → Edit. Validation is LOCALE-NESTED: `<locale>.slug`, `<locale>.name`, `<locale>.description` (when display_mode requires it) are required. Top-level fields: `position`, `attributes`, `parent_id`, `display_mode`, `status`. Moving a category is done via this endpoint with `parent_id` + `position` (no separate /move endpoint — parity with Bagisto admin which has no move action).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Category ID.', true, schema: ['type' => 'integer', 'example' => 7]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['position', 'attributes'],
                                'properties' => [
                                    'locale' => ['type' => 'string', 'example' => 'en'],
                                    'position' => ['type' => 'integer', 'example' => 2],
                                    'attributes' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [11, 23]],
                                    'parent_id' => ['type' => 'integer', 'nullable' => true, 'example' => 1],
                                    'status' => ['type' => 'integer', 'example' => 1],
                                    'en' => [
                                        'type' => 'object',
                                        'example' => [
                                            'slug' => 'apparel',
                                            'name' => 'Apparel',
                                            'description' => "Men's and women's apparel",
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Category updated. Returns the same shape as GET /catalog/categories/{id}.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 7, 'name' => 'Apparel', 'slug' => 'apparel', 'position' => 2],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Category not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/catalog/categories/{id}',
            provider: AdminCategoryWriteProvider::class,
            processor: AdminCategoryProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'Delete a category',
                description: 'Refuses with HTTP 400 if the category is the root (id=1) or referenced as `channels.root_category_id`.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Category ID.', true, schema: ['type' => 'integer', 'example' => 7]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Category deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Category deleted successfully.'],
                            ],
                        ]),
                    ),
                    '400' => new Model\Response(
                        description: 'Cannot delete — root category or a channel root.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['type' => '/errors/400', 'status' => 400, 'detail' => 'Root and channel-root categories cannot be deleted.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Category not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/catalog/categories/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminCategoryItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'Category detail with all translations',
                description: 'Returns one category with the full translations array and the list of filterable attribute IDs.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Category ID.', true, schema: ['type' => 'integer', 'example' => 7]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Single category with all locale translations inlined and filterable attribute IDs.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 7,
                                    'position' => 1,
                                    'status' => 1,
                                    'parentId' => 1,
                                    'displayMode' => 'products_and_description',
                                    'logoUrl' => 'https://example.com/storage/category/7/logo.webp',
                                    'bannerUrl' => null,
                                    'name' => 'Apparel',
                                    'slug' => 'apparel',
                                    'description' => "Men's and women's apparel",
                                    'locale' => 'en',
                                    'createdAt' => '2026-01-12T08:15:00+00:00',
                                    'updatedAt' => '2026-04-30T14:20:09+00:00',
                                    'translations' => [
                                        ['locale' => 'en', 'name' => 'Apparel', 'slug' => 'apparel', 'description' => "Men's and women's apparel", 'metaTitle' => null, 'metaDescription' => null, 'metaKeywords' => null],
                                        ['locale' => 'fr', 'name' => 'Vêtements', 'slug' => 'vetements', 'description' => null, 'metaTitle' => null, 'metaDescription' => null, 'metaKeywords' => null],
                                    ],
                                    'filterableAttributeIds' => [11, 23],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(
                        description: 'Category not found.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'type' => '/errors/404',
                                    'title' => 'An error occurred',
                                    'status' => 404,
                                    'detail' => 'Category not found',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/catalog/categories',
            provider: AdminCategoryCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Categories'],
                summary: 'List categories (datagrid parity)',
                description: 'Paginated, filterable, sortable category list mirroring the admin Catalog → Categories datagrid.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('category_id', 'query', 'Filter by category ID — single integer or comma-separated list (e.g. "12" or "12,18").', false, schema: ['type' => 'string', 'example' => '12']),
                    new Model\Parameter('name', 'query', 'Partial category name match (SQL LIKE).', false, schema: ['type' => 'string', 'example' => 'Apparel']),
                    new Model\Parameter('position', 'query', 'Exact position filter.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('status', 'query', 'Filter by status (0 = disabled, 1 = enabled).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 1]),
                    new Model\Parameter('parent_id', 'query', 'Filter by parent category ID.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('locale', 'query', 'Locale code for translation resolution (e.g. "en").', false, schema: ['type' => 'string', 'example' => 'en']),
                    new Model\Parameter('sort', 'query', 'Column to sort by.', false, schema: ['type' => 'string', 'enum' => ['id', 'name', 'position', 'status'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of category rows in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 7,
                                            'position' => 1,
                                            'status' => 1,
                                            'parentId' => 1,
                                            'displayMode' => 'products_and_description',
                                            'logoUrl' => null,
                                            'bannerUrl' => null,
                                            'name' => 'Apparel',
                                            'slug' => 'apparel',
                                            'description' => "Men's and women's apparel",
                                            'locale' => 'en',
                                            'createdAt' => '2026-01-12T08:15:00+00:00',
                                            'updatedAt' => '2026-04-30T14:20:09+00:00',
                                            'translations' => null,
                                            'filterableAttributeIds' => null,
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
            provider: AdminCategoryCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'category_id' => ['type' => 'String'],
                'name' => ['type' => 'String'],
                'position' => ['type' => 'Int'],
                'status' => ['type' => 'Int'],
                'locale' => ['type' => 'String'],
                'parent_id' => ['type' => 'Int'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin catalog categories listing (cursor pagination). Mirrors the REST GET /api/admin/catalog/categories.',
        ),
        new Query(
            provider: AdminCategoryItemProvider::class,
            description: 'Admin catalog category detail by id, with all translations inlined.',
        ),
        new Mutation(
            name: 'create',
            input: AdminCategoryCreateInput::class,
            processor: AdminCategoryProcessor::class,
            description: 'Create a new category. Becomes createAdminCategory.',
        ),
        new Mutation(
            name: 'update',
            input: AdminCategoryUpdateInput::class,
            processor: AdminCategoryProcessor::class,
            description: 'Update a category. Becomes updateAdminCategory. Move-by-parent_id is also handled here (no separate move mutation).',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCategoryUpdateInput::class,
            processor: AdminCategoryProcessor::class,
            description: 'Delete a category. Becomes deleteAdminCategory. Refused for root or channel-root.',
        ),
    ],
)]
class AdminCategory
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $position = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?int $parent_id = null;

    #[ApiProperty(writable: false)]
    public ?string $display_mode = null;

    #[ApiProperty(writable: false)]
    public ?string $logo_url = null;

    #[ApiProperty(writable: false)]
    public ?string $banner_url = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $slug = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    /** @var array<int, mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    /** @var array<int, int>|null */
    #[ApiProperty(writable: false)]
    public ?array $filterable_attribute_ids = null;
}
