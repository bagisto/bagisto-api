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
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxCategoryCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxCategoryUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryItemProvider;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryProcessor;
use Webkul\BagistoApi\Admin\State\AdminSettingsTaxCategoryWriteProvider;

/**
 * Admin Settings → Tax Categories endpoints (Block B Wave 3).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\Tax\TaxCategoryController 1:1.
 *
 * REST:
 *   GET    /api/admin/settings/tax-categories
 *   GET    /api/admin/settings/tax-categories/{id}
 *   POST   /api/admin/settings/tax-categories
 *   PUT    /api/admin/settings/tax-categories/{id}
 *   DELETE /api/admin/settings/tax-categories/{id}
 *
 * GraphQL:
 *   adminSettingsTaxCategories       — cursor listing
 *   adminSettingsTaxCategory(id:)    — detail
 *   createAdminSettingsTaxCategory
 *   updateAdminSettingsTaxCategory
 *   deleteAdminSettingsTaxCategory
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminSettingsTaxCategory',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/settings/tax-categories',
            input: AdminSettingsTaxCategoryCreateInput::class,
            processor: AdminSettingsTaxCategoryProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Create a tax category',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['code', 'name', 'description', 'taxrates'],
                                'properties' => [
                                    'code'        => ['type' => 'string', 'example' => 'reduced-rate'],
                                    'name'        => ['type' => 'string', 'example' => 'Reduced Rate'],
                                    'description' => ['type' => 'string', 'example' => 'Reduced VAT for essentials'],
                                    'taxrates'    => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1, 2]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Tax category created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/settings/tax-categories/{id}',
            input: AdminSettingsTaxCategoryUpdateInput::class,
            provider: AdminSettingsTaxCategoryWriteProvider::class,
            processor: AdminSettingsTaxCategoryProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Update a tax category',
                description: 'Code uniqueness excludes the current id. Re-syncs the tax_rates pivot to the supplied taxrates list.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['code', 'name', 'description', 'taxrates'],
                                'properties' => [
                                    'code'        => ['type' => 'string', 'example' => 'reduced-rate'],
                                    'name'        => ['type' => 'string', 'example' => 'Reduced Rate'],
                                    'description' => ['type' => 'string', 'example' => 'Reduced VAT for essentials'],
                                    'taxrates'    => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Tax category updated.'),
                    '404' => new Model\Response(description: 'Tax category not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/settings/tax-categories/{id}',
            provider: AdminSettingsTaxCategoryWriteProvider::class,
            processor: AdminSettingsTaxCategoryProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Delete a tax category',
                description: 'Mirrors monolith TaxCategoryController::destroy — refuses with HTTP 400 if any tax_rates are still attached to the category.',
                responses: [
                    '200' => new Model\Response(description: 'Tax category deleted.'),
                    '400' => new Model\Response(description: 'Cannot delete — tax rates still attached.'),
                    '404' => new Model\Response(description: 'Tax category not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/settings/tax-categories/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminSettingsTaxCategoryItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'Tax category detail',
                responses: [
                    '200' => new Model\Response(
                        description: 'Single tax category including attached tax_rates.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'          => 1,
                                    'code'        => 'reduced-rate',
                                    'name'        => 'Reduced Rate',
                                    'description' => 'Reduced VAT for essentials',
                                    'taxRates'    => [
                                        ['id' => 1, 'identifier' => 'IN-VAT-5', 'taxRate' => 5.0],
                                    ],
                                    'createdAt' => '2026-04-30T14:20:09+00:00',
                                    'updatedAt' => '2026-04-30T14:20:09+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Tax category not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/settings/tax-categories',
            provider: AdminSettingsTaxCategoryCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings'],
                summary: 'List tax categories (datagrid parity)',
                description: 'Paginated, filterable, sortable tax categories list. Filters: code, name (LIKE). Sort: id (default desc), code, name.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('code', 'query', 'Partial code match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'code', 'name']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Paginated list in the { data, meta } envelope.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminSettingsTaxCategoryCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'code'  => ['type' => 'String'],
                'name'  => ['type' => 'String'],
                'sort'  => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin tax categories listing (cursor pagination).',
        ),
        new Query(
            provider: AdminSettingsTaxCategoryItemProvider::class,
            description: 'Admin tax category detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminSettingsTaxCategoryCreateInput::class,
            processor: AdminSettingsTaxCategoryProcessor::class,
            description: 'Create a tax category. Becomes createAdminSettingsTaxCategory.',
        ),
        new Mutation(
            name: 'update',
            input: AdminSettingsTaxCategoryUpdateInput::class,
            processor: AdminSettingsTaxCategoryProcessor::class,
            description: 'Update a tax category. Becomes updateAdminSettingsTaxCategory.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminSettingsTaxCategoryUpdateInput::class,
            processor: AdminSettingsTaxCategoryProcessor::class,
            description: 'Delete a tax category. Refused if any tax rates remain attached.',
        ),
    ],
)]
class AdminSettingsTaxCategory
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $code = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    /**
     * @var array<int, array{id:int, identifier:string|null, taxRate:float|null}>|null
     */
    #[ApiProperty(writable: false)]
    public ?array $taxRates = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;
}
