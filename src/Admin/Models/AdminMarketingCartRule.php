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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCopyInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCopyProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleWriteProvider;

/**
 * Admin Marketing → Cart Rules CRUD endpoints (Block F1b).
 *
 * REST:
 *   GET    /api/admin/marketing/cart-rules
 *   GET    /api/admin/marketing/cart-rules/{id}
 *   POST   /api/admin/marketing/cart-rules
 *   PUT    /api/admin/marketing/cart-rules/{id}
 *   DELETE /api/admin/marketing/cart-rules/{id}
 *
 * GraphQL: adminMarketingCartRules, adminMarketingCartRule,
 *          createAdminMarketingCartRule, updateAdminMarketingCartRule,
 *          deleteAdminMarketingCartRule
 *
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Promotions\CartRuleController.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCartRule',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/cart-rules',
            input: AdminMarketingCartRuleCreateInput::class,
            processor: AdminMarketingCartRuleProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Create a new cart rule',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'channels', 'customer_groups', 'coupon_type', 'action_type', 'discount_amount'],
                                'properties' => [
                                    'name'                => ['type' => 'string', 'example' => '10% off summer'],
                                    'description'         => ['type' => 'string', 'example' => 'Sitewide 10% off summer collection'],
                                    'channels'            => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'customer_groups'     => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1, 2, 3]],
                                    'coupon_type'         => ['type' => 'integer', 'enum' => [0, 1], 'description' => '0 = no coupon (auto-applied), 1 = specific coupon.', 'example' => 1],
                                    'use_auto_generation' => ['type' => 'integer', 'enum' => [0, 1], 'description' => 'When coupon_type=1: 1 = auto-generate codes, 0 = use the supplied coupon_code.', 'example' => 0],
                                    'coupon_code'         => ['type' => 'string', 'description' => 'Required when coupon_type=1 and use_auto_generation=0; must be unique.', 'example' => 'SUMMER10'],
                                    'uses_per_coupon'     => ['type' => 'integer', 'description' => 'Total redemptions allowed per coupon (0 = unlimited).', 'example' => 100],
                                    'usage_per_customer'  => ['type' => 'integer', 'description' => 'Redemptions allowed per customer (0 = unlimited).', 'example' => 1],
                                    'condition_type'      => ['type' => 'integer', 'enum' => [1, 2], 'description' => '1 = all conditions true, 2 = any condition true.', 'example' => 1],
                                    'conditions'          => ['type' => 'array', 'items' => ['type' => 'object'], 'description' => 'Condition rows: { attribute, operator, value, attribute_type }.', 'example' => [['attribute' => 'cart|base_sub_total', 'operator' => '>=', 'value' => '100', 'attribute_type' => 'price']]],
                                    'action_type'         => ['type' => 'string', 'enum' => ['by_percent', 'by_fixed', 'cart_fixed', 'buy_x_get_y'], 'example' => 'by_percent'],
                                    'discount_amount'     => ['type' => 'number', 'description' => '0-100 when action_type=by_percent.', 'example' => 10],
                                    'discount_quantity'   => ['type' => 'integer', 'description' => 'Max quantity discounted (buy_x_get_y).', 'example' => 1],
                                    'discount_step'       => ['type' => 'integer', 'description' => 'Buy-X step (buy_x_get_y).', 'example' => 0],
                                    'apply_to_shipping'   => ['type' => 'integer', 'enum' => [0, 1], 'description' => 'Apply the discount to shipping too.', 'example' => 0],
                                    'free_shipping'       => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'end_other_rules'     => ['type' => 'integer', 'enum' => [0, 1], 'description' => 'Stop processing further rules when this one matches.', 'example' => 0],
                                    'sort_order'          => ['type' => 'integer', 'description' => 'Priority (lower runs first).', 'example' => 0],
                                    'starts_from'         => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-06-01 00:00:00'],
                                    'ends_till'           => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-08-31 23:59:59'],
                                    'status'              => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(description: 'Cart rule created.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/cart-rules/{id}',
            input: AdminMarketingCartRuleUpdateInput::class,
            provider: AdminMarketingCartRuleWriteProvider::class,
            processor: AdminMarketingCartRuleProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Update a cart rule',
                description: 'Partial update — send only the fields you change. channels / customer_groups, when supplied, fully replace the current set.',
                parameters: [new Model\Parameter('id', 'path', 'Cart rule ID.', true, schema: ['type' => 'integer'])],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name'                => ['type' => 'string', 'example' => '15% off summer'],
                                    'description'         => ['type' => 'string', 'example' => 'Updated description'],
                                    'channels'            => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'customer_groups'     => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1, 2, 3]],
                                    'coupon_type'         => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'use_auto_generation' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'coupon_code'         => ['type' => 'string', 'example' => 'SUMMER15'],
                                    'uses_per_coupon'     => ['type' => 'integer', 'example' => 100],
                                    'usage_per_customer'  => ['type' => 'integer', 'example' => 1],
                                    'condition_type'      => ['type' => 'integer', 'enum' => [1, 2], 'example' => 1],
                                    'conditions'          => ['type' => 'array', 'items' => ['type' => 'object'], 'example' => [['attribute' => 'cart|base_sub_total', 'operator' => '>=', 'value' => '100', 'attribute_type' => 'price']]],
                                    'action_type'         => ['type' => 'string', 'enum' => ['by_percent', 'by_fixed', 'cart_fixed', 'buy_x_get_y'], 'example' => 'by_percent'],
                                    'discount_amount'     => ['type' => 'number', 'example' => 15],
                                    'discount_quantity'   => ['type' => 'integer', 'example' => 1],
                                    'discount_step'       => ['type' => 'integer', 'example' => 0],
                                    'apply_to_shipping'   => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'free_shipping'       => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'end_other_rules'     => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'sort_order'          => ['type' => 'integer', 'example' => 0],
                                    'starts_from'         => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-06-01 00:00:00'],
                                    'ends_till'           => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-08-31 23:59:59'],
                                    'status'              => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/cart-rules/{id}',
            provider: AdminMarketingCartRuleWriteProvider::class,
            processor: AdminMarketingCartRuleProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Delete a cart rule',
                parameters: [new Model\Parameter('id', 'path', 'Cart rule ID.', true, schema: ['type' => 'integer'])],
                responses: [
                    '200' => new Model\Response(description: 'Deleted.'),
                    '404' => new Model\Response(description: 'Not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/cart-rules/{id}',
            provider: AdminMarketingCartRuleItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Cart rule detail',
                parameters: [new Model\Parameter('id', 'path', 'Cart rule ID.', true, schema: ['type' => 'integer'])],
            ),
        ),
        new Post(
            uriTemplate: '/marketing/cart-rules/{id}/copy',
            requirements: ['id' => '\d+'],
            input: AdminMarketingCartRuleCopyInput::class,
            processor: AdminMarketingCartRuleCopyProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Copy a cart rule',
                description: 'Duplicates the cart rule with status forced inactive and the name prefixed "Copy of ...", copies its channel and customer-group assignments, and returns the new rule\'s full detail (prefilled for editing). Coupons are not copied. Mirrors the admin datagrid Copy action.',
                parameters: [new Model\Parameter('id', 'path', 'Source cart rule ID.', true, schema: ['type' => 'integer', 'example' => 17])],
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Cart rule copied; returns the new rule detail.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks marketing.promotions.cart_rules.create.'),
                    '404' => new Model\Response(description: 'Source cart rule not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/cart-rules',
            provider: AdminMarketingCartRuleCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'List cart rules',
                description: 'Paginated, filterable, sortable list. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'query', 'Filter by ID (single or comma-separated).', false, schema: ['type' => 'string']),
                    new Model\Parameter('name', 'query', 'Filter by name (partial match).', false, schema: ['type' => 'string']),
                    new Model\Parameter('coupon_code', 'query', 'Filter by coupon code (partial match).', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Filter by status (0/1).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('coupon_type', 'query', 'Filter by coupon_type (1/2).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('sort_order', 'query', 'Filter by priority (sort_order, exact).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('starts_from_from', 'query', 'Start date >= (ISO 8601).', false, schema: ['type' => 'string', 'format' => 'date-time']),
                    new Model\Parameter('starts_from_to', 'query', 'Start date <= (ISO 8601).', false, schema: ['type' => 'string', 'format' => 'date-time']),
                    new Model\Parameter('ends_till_from', 'query', 'End date >= (ISO 8601).', false, schema: ['type' => 'string', 'format' => 'date-time']),
                    new Model\Parameter('ends_till_to', 'query', 'End date <= (ISO 8601).', false, schema: ['type' => 'string', 'format' => 'date-time']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'name', 'sort_order']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminMarketingCartRuleCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'id'               => ['type' => 'String'],
                'name'             => ['type' => 'String'],
                'coupon_code'      => ['type' => 'String'],
                'status'           => ['type' => 'Int'],
                'coupon_type'      => ['type' => 'Int'],
                'sort_order'       => ['type' => 'Int'],
                'starts_from_from' => ['type' => 'String'],
                'starts_from_to'   => ['type' => 'String'],
                'ends_till_from'   => ['type' => 'String'],
                'ends_till_to'     => ['type' => 'String'],
                'sort'             => ['type' => 'String'],
                'order'            => ['type' => 'String'],
            ],
            description: 'Admin marketing cart rules listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingCartRuleItemProvider::class,
            description: 'Admin marketing cart rule detail by id.',
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingCartRuleCreateInput::class,
            processor: AdminMarketingCartRuleProcessor::class,
            description: 'Becomes createAdminMarketingCartRule.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingCartRuleUpdateInput::class,
            processor: AdminMarketingCartRuleProcessor::class,
            description: 'Becomes updateAdminMarketingCartRule.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingCartRuleUpdateInput::class,
            processor: AdminMarketingCartRuleProcessor::class,
            description: 'Becomes deleteAdminMarketingCartRule.',
        ),
        new Mutation(
            name: 'copy',
            input: AdminMarketingCartRuleCopyInput::class,
            processor: AdminMarketingCartRuleCopyProcessor::class,
            description: 'Becomes copyAdminMarketingCartRule. Input: { cartRuleId }. Returns the new rule detail.',
        ),
    ],
)]
class AdminMarketingCartRule
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $starts_from = null;

    #[ApiProperty(writable: false)]
    public ?string $ends_till = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?int $coupon_type = null;

    #[ApiProperty(writable: false)]
    public ?int $use_auto_generation = null;

    #[ApiProperty(writable: false)]
    public ?int $usage_per_customer = null;

    #[ApiProperty(writable: false)]
    public ?int $uses_per_coupon = null;

    #[ApiProperty(writable: false)]
    public ?int $times_used = null;

    #[ApiProperty(writable: false)]
    public ?int $condition_type = null;

    /** @var array<int,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $conditions = null;

    #[ApiProperty(writable: false)]
    public ?string $action_type = null;

    #[ApiProperty(writable: false)]
    public ?float $discount_amount = null;

    #[ApiProperty(writable: false)]
    public ?int $discount_quantity = null;

    #[ApiProperty(writable: false)]
    public ?string $discount_step = null;

    #[ApiProperty(writable: false)]
    public ?int $apply_to_shipping = null;

    #[ApiProperty(writable: false)]
    public ?int $free_shipping = null;

    #[ApiProperty(writable: false)]
    public ?int $end_other_rules = null;

    #[ApiProperty(writable: false)]
    public ?int $uses_attribute_conditions = null;

    #[ApiProperty(writable: false)]
    public ?int $sort_order = null;

    #[ApiProperty(writable: false)]
    public ?string $coupon_code = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $channels = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $customer_groups = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;
}
