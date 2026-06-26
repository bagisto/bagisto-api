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
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxCategoryCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxCategoryRestDto;
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
            output: AdminSettingsTaxCategoryRestDto::class,
            processor: AdminSettingsTaxCategoryProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Tax Categories'],
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
                tags: ['Admin Settings: Tax Categories'],
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
                tags: ['Admin Settings: Tax Categories'],
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
            output: AdminSettingsTaxCategoryRestDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Tax Categories'],
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
            output: AdminSettingsTaxCategoryRestDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Settings: Tax Categories'],
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
class AdminSettingsTaxCategory extends EloquentModel
{
    /** @var string */
    protected $table = 'tax_categories';

    /** @var array */
    protected $casts = [
        'id'         => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array */
    protected $appends = ['message'];

    public ?string $actionMessage = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false, example: 'Tax category deleted successfully.')]
    public function getMessageAttribute(): ?string
    {
        return $this->actionMessage;
    }

    /**
     * Attached tax rates (GraphQL connection — `taxRates { edges { node } }`).
     * The relation METHOD must be snake_case (`tax_rates`) so the central
     * converter resolves it; the GraphQL field surfaces as `taxRates`.
     *
     * HasMany over the pivot table (NOT belongsToMany): the pivot carries its own
     * `id` column, which a belongsToMany connection node would wrongly resolve as
     * the node `_id`. The sub-resource reads the pivot row and surfaces the rate
     * (id = tax_rate_id) so `node { _id }` is the real rate id.
     */
    #[ApiProperty(writable: false)]
    public function tax_rates(): HasMany
    {
        return $this->hasMany(AdminSettingsTaxRateRef::class, 'tax_category_id')
            ->select(
                'tax_categories_tax_rates.tax_category_id',
                'tax_categories_tax_rates.tax_rate_id',
                'tax_categories_tax_rates.tax_rate_id as id',
            );
    }
}
