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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCatalogRuleUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCatalogRuleWriteProvider;

/**
 * Admin Marketing → Catalog Rules endpoints (Block F1a; objectified 2026-06-23).
 *
 * Bare Eloquent `#[ApiResource]` parent. The assigned channels / customer groups
 * are field-selectable:
 *   GraphQL → `channels { edges { node { id code name } } }` and
 *             `customerGroups { edges { node { id code name } } }` Relay connections.
 *   REST    → the same data as flat arrays of objects `[{id, code, name}]`.
 *
 * BREAKING (user-approved): the old bare int arrays `channels: [1]` /
 * `customerGroups: [2]` are REPLACED by the object connections (GraphQL) /
 * object arrays (REST). `conditions` stays a JSON scalar (dynamic rule rows).
 *
 * REST shape stays flat via `output: AdminMarketingCatalogRuleRestDto`; GraphQL
 * ops carry NO output so they return this Eloquent model → connections resolve.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Promotions\CatalogRuleController 1:1.
 *
 * REST:
 *   GET    /api/admin/marketing/catalog-rules
 *   GET    /api/admin/marketing/catalog-rules/{id}
 *   POST   /api/admin/marketing/catalog-rules
 *   PUT    /api/admin/marketing/catalog-rules/{id}
 *   DELETE /api/admin/marketing/catalog-rules/{id}
 *
 * GraphQL:
 *   adminMarketingCatalogRules / adminMarketingCatalogRule(id:)
 *   createAdminMarketingCatalogRule / updateAdminMarketingCatalogRule / deleteAdminMarketingCatalogRule
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCatalogRule',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/catalog-rules',
            input: AdminMarketingCatalogRuleCreateInput::class,
            output: AdminMarketingCatalogRuleRestDto::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Create a catalog rule',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'channels', 'customer_groups', 'action_type', 'discount_amount'],
                                'properties' => [
                                    'name'            => ['type' => 'string', 'example' => 'Summer 10% off'],
                                    'description'     => ['type' => 'string', 'example' => 'Sitewide 10% off summer collection'],
                                    'starts_from'     => ['type' => 'string', 'example' => '2026-06-01'],
                                    'ends_till'       => ['type' => 'string', 'example' => '2026-08-31'],
                                    'status'          => ['type' => 'integer', 'example' => 1],
                                    'sort_order'      => ['type' => 'integer', 'example' => 0],
                                    'condition_type'  => ['type' => 'integer', 'example' => 1],
                                    'conditions'      => ['type' => 'array', 'items' => ['type' => 'object'], 'example' => []],
                                    'end_other_rules' => ['type' => 'integer', 'example' => 0],
                                    'action_type'     => ['type' => 'string', 'example' => 'by_percent'],
                                    'discount_amount' => ['type' => 'number', 'example' => 10],
                                    'channels'        => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Assigned channel ids (request stays id-based; the response returns objects).', 'example' => [1]],
                                    'customer_groups' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Assigned customer-group ids (request stays id-based; the response returns objects).', 'example' => [1, 2]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Catalog rule created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'             => 126,
                                    'name'           => 'Summer Collection 10% Off',
                                    'description'    => 'Sitewide 10% off the summer collection',
                                    'startsFrom'     => null,
                                    'endsTill'       => null,
                                    'status'         => 1,
                                    'sortOrder'      => 0,
                                    'conditionType'  => 1,
                                    'conditions'     => [],
                                    'endOtherRules'  => 0,
                                    'actionType'     => 'by_percent',
                                    'discountAmount' => 10,
                                    'channels'       => [['id' => 1, 'code' => 'default', 'name' => 'Default']],
                                    'customerGroups' => [['id' => 1, 'code' => 'guest', 'name' => 'Guest'], ['id' => 2, 'code' => 'general', 'name' => 'General']],
                                    'createdAt'      => '2026-06-17T12:13:15+05:30',
                                    'updatedAt'      => '2026-06-17T12:13:15+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/catalog-rules/{id}',
            input: AdminMarketingCatalogRuleUpdateInput::class,
            output: AdminMarketingCatalogRuleRestDto::class,
            provider: AdminMarketingCatalogRuleWriteProvider::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Update a catalog rule',
                description: 'Partial update — send only the fields you change. channels + customer_groups, when supplied, fully replace the current pivots.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name'            => ['type' => 'string', 'example' => 'Summer 15% off'],
                                    'description'     => ['type' => 'string', 'example' => 'Updated description'],
                                    'channels'        => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'customer_groups' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1, 2]],
                                    'condition_type'  => ['type' => 'integer', 'enum' => [1, 2], 'example' => 1],
                                    'conditions'      => ['type' => 'array', 'items' => ['type' => 'object'], 'example' => [['attribute' => 'product|category_ids', 'operator' => '{}', 'value' => '5', 'attribute_type' => 'select']]],
                                    'end_other_rules' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'action_type'     => ['type' => 'string', 'enum' => ['by_percent', 'by_fixed'], 'example' => 'by_percent'],
                                    'discount_amount' => ['type' => 'number', 'example' => 15],
                                    'sort_order'      => ['type' => 'integer', 'example' => 0],
                                    'starts_from'     => ['type' => 'string', 'example' => '2026-06-01'],
                                    'ends_till'       => ['type' => 'string', 'example' => '2026-08-31'],
                                    'status'          => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Catalog rule updated; returns the updated detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'             => 126,
                                    'name'           => 'Summer 15% off',
                                    'description'    => 'Updated description',
                                    'startsFrom'     => null,
                                    'endsTill'       => null,
                                    'status'         => 1,
                                    'sortOrder'      => 0,
                                    'conditionType'  => 1,
                                    'conditions'     => [],
                                    'endOtherRules'  => 0,
                                    'actionType'     => 'by_percent',
                                    'discountAmount' => 15,
                                    'channels'       => [['id' => 1, 'code' => 'default', 'name' => 'Default']],
                                    'customerGroups' => [['id' => 1, 'code' => 'guest', 'name' => 'Guest'], ['id' => 2, 'code' => 'general', 'name' => 'General']],
                                    'createdAt'      => '2026-06-17T12:13:15+05:30',
                                    'updatedAt'      => '2026-06-17T13:01:42+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Catalog rule not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/catalog-rules/{id}',
            provider: AdminMarketingCatalogRuleWriteProvider::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Delete a catalog rule',
                responses: [
                    '200' => new Model\Response(
                        description: 'Catalog rule deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Catalog rule deleted.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Catalog rule not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/catalog-rules/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminMarketingCatalogRuleItemProvider::class,
            output: AdminMarketingCatalogRuleRestDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Catalog rule detail',
                responses: [
                    '200' => new Model\Response(
                        description: 'Single catalog rule with channels + customerGroups (object arrays) + conditions.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'             => 126,
                                    'name'           => 'Summer Collection 10% Off',
                                    'description'    => 'Sitewide 10% off the summer collection',
                                    'startsFrom'     => null,
                                    'endsTill'       => null,
                                    'status'         => 1,
                                    'sortOrder'      => 0,
                                    'conditionType'  => 1,
                                    'conditions'     => [],
                                    'endOtherRules'  => 0,
                                    'actionType'     => 'by_percent',
                                    'discountAmount' => 10,
                                    'channels'       => [['id' => 1, 'code' => 'default', 'name' => 'Default']],
                                    'customerGroups' => [['id' => 2, 'code' => 'general', 'name' => 'General']],
                                    'createdAt'      => '2026-06-17T12:13:15+05:30',
                                    'updatedAt'      => '2026-06-17T12:13:15+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Catalog rule not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/catalog-rules',
            provider: AdminMarketingCatalogRuleCollectionProvider::class,
            output: AdminMarketingCatalogRuleRestDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'List catalog rules',
                description: 'Filters: name (LIKE), status. Sort: id (default desc), name, sort_order.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('id', 'query', 'Filter by ID (single or comma-separated).', false, schema: ['type' => 'string']),
                    new Model\Parameter('name', 'query', 'Partial name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Enabled flag (0/1).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('sort_order', 'query', 'Filter by priority (sort_order, exact).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('starts_from_from', 'query', 'Start date >= (ISO 8601).', false, schema: ['type' => 'string', 'format' => 'date-time']),
                    new Model\Parameter('starts_from_to', 'query', 'Start date <= (ISO 8601).', false, schema: ['type' => 'string', 'format' => 'date-time']),
                    new Model\Parameter('ends_till_from', 'query', 'End date >= (ISO 8601).', false, schema: ['type' => 'string', 'format' => 'date-time']),
                    new Model\Parameter('ends_till_to', 'query', 'End date <= (ISO 8601).', false, schema: ['type' => 'string', 'format' => 'date-time']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'name', 'sort_order']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list in the { data, meta } envelope. conditions / channels / customerGroups are detail-only and null on list rows.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'             => 126,
                                            'name'           => 'Summer Collection 10% Off',
                                            'description'    => 'Sitewide 10% off the summer collection',
                                            'startsFrom'     => null,
                                            'endsTill'       => null,
                                            'status'         => 1,
                                            'sortOrder'      => 0,
                                            'conditionType'  => 1,
                                            'conditions'     => null,
                                            'endOtherRules'  => 0,
                                            'actionType'     => 'by_percent',
                                            'discountAmount' => 10,
                                            'channels'       => null,
                                            'customerGroups' => null,
                                            'createdAt'      => '2026-06-17T12:13:15+05:30',
                                            'updatedAt'      => '2026-06-17T12:13:15+05:30',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 10,
                                        'lastPage'    => 38,
                                        'total'       => 38,
                                        'from'        => 1,
                                        'to'          => 1,
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
            provider: AdminMarketingCatalogRuleCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'id'               => ['type' => 'String'],
                'name'             => ['type' => 'String'],
                'status'           => ['type' => 'Int'],
                'sort_order'       => ['type' => 'Int'],
                'starts_from_from' => ['type' => 'String'],
                'starts_from_to'   => ['type' => 'String'],
                'ends_till_from'   => ['type' => 'String'],
                'ends_till_to'     => ['type' => 'String'],
                'sort'             => ['type' => 'String'],
                'order'            => ['type' => 'String'],
            ],
            description: 'Admin catalog rules listing (cursor pagination). channels / customerGroups connections are detail-only (empty on list rows).',
        ),
        new Query(
            provider: AdminMarketingCatalogRuleItemProvider::class,
            description: 'Admin catalog rule detail by id. Sub-select channels { edges { node } } and customerGroups { edges { node } }.',
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingCatalogRuleCreateInput::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            description: 'Create a catalog rule. Becomes createAdminMarketingCatalogRule.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingCatalogRuleUpdateInput::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            description: 'Update a catalog rule. Becomes updateAdminMarketingCatalogRule.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingCatalogRuleUpdateInput::class,
            processor: AdminMarketingCatalogRuleProcessor::class,
            description: 'Delete a catalog rule. Becomes deleteAdminMarketingCatalogRule.',
        ),
    ],
)]
class AdminMarketingCatalogRule extends EloquentModel
{
    /** @var string */
    protected $table = 'catalog_rules';

    /** @var array */
    protected $casts = [
        'id'              => 'int',
        'status'          => 'int',
        'condition_type'  => 'int',
        'end_other_rules' => 'int',
        'sort_order'      => 'int',
        'discount_amount' => 'float',
        'conditions'      => 'array',
        'starts_from'     => 'datetime',
        'ends_till'       => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /** @var array */
    protected $appends = ['message'];

    /** Success signal — populated only on the delete mutation result. */
    public ?string $actionMessage = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    #[ApiProperty(writable: false)]
    public function getMessageAttribute(): ?string
    {
        return $this->actionMessage;
    }

    /**
     * Assigned channels (GraphQL connection — `channels { edges }`). belongsToMany
     * over `catalog_rule_channels` — the pivot has no own id, so the node `_id` is
     * the channel's real id.
     */
    #[ApiProperty(writable: false)]
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminMarketingChannelRef::class,
            'catalog_rule_channels',
            'catalog_rule_id',
            'channel_id',
        );
    }

    /**
     * Assigned customer groups (GraphQL connection — `customerGroups { edges }`).
     * The relation METHOD is snake_case (`customer_groups`) so the central
     * converter resolves it; the GraphQL field surfaces as `customerGroups`.
     */
    #[ApiProperty(writable: false)]
    public function customer_groups(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminMarketingCustomerGroupRef::class,
            'catalog_rule_customer_groups',
            'catalog_rule_id',
            'customer_group_id',
        );
    }
}
