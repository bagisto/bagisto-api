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
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCopyInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCopyProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleWriteProvider;

/**
 * Admin Marketing → Cart Rules CRUD endpoints (Block F1b; objectified 2026-06-23).
 *
 * Bare Eloquent `#[ApiResource]` parent. The assigned channels / customer groups
 * are field-selectable:
 *   GraphQL → `channels { edges { node { id code name } } }` and
 *             `customerGroups { edges { node { id code name } } }` Relay connections.
 *   REST    → the same data as flat arrays of objects `[{id, code, name}]`.
 *
 * BREAKING (user-approved): the old bare int arrays `channels: [1]` /
 * `customerGroups: [2]` are REPLACED by the object connections (GraphQL) /
 * object arrays (REST). `conditions` stays a JSON scalar (dynamic rule rows);
 * `couponCode` stays a scalar string (primary coupon).
 *
 * REST shape stays flat via `output: AdminMarketingCartRuleRestDto`; GraphQL ops
 * carry NO output so they return this Eloquent model → connections resolve.
 *
 * REST:
 *   GET    /api/admin/marketing/cart-rules
 *   GET    /api/admin/marketing/cart-rules/{id}
 *   POST   /api/admin/marketing/cart-rules
 *   PUT    /api/admin/marketing/cart-rules/{id}
 *   DELETE /api/admin/marketing/cart-rules/{id}
 *   POST   /api/admin/marketing/cart-rules/{id}/copy
 *
 * GraphQL: adminMarketingCartRules, adminMarketingCartRule,
 *          createAdminMarketingCartRule, updateAdminMarketingCartRule,
 *          deleteAdminMarketingCartRule, copyAdminMarketingCartRule
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
            output: AdminMarketingCartRuleRestDto::class,
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
                                'type' => 'object',
                                'required' => ['name', 'channels', 'customer_groups', 'coupon_type', 'action_type', 'discount_amount'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => '10% off summer'],
                                    'description' => ['type' => 'string', 'example' => 'Sitewide 10% off summer collection'],
                                    'channels' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Assigned channel ids (request stays id-based; the response returns objects).', 'example' => [1]],
                                    'customer_groups' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Assigned customer-group ids (request stays id-based; the response returns objects).', 'example' => [1, 2, 3]],
                                    'coupon_type' => ['type' => 'integer', 'enum' => [0, 1], 'description' => '0 = no coupon (auto-applied), 1 = specific coupon.', 'example' => 1],
                                    'use_auto_generation' => ['type' => 'integer', 'enum' => [0, 1], 'description' => 'When coupon_type=1: 1 = auto-generate codes, 0 = use the supplied coupon_code.', 'example' => 0],
                                    'coupon_code' => ['type' => 'string', 'description' => 'Required when coupon_type=1 and use_auto_generation=0; must be unique.', 'example' => 'SUMMER10'],
                                    'uses_per_coupon' => ['type' => 'integer', 'description' => 'Total redemptions allowed per coupon (0 = unlimited).', 'example' => 100],
                                    'usage_per_customer' => ['type' => 'integer', 'description' => 'Redemptions allowed per customer (0 = unlimited).', 'example' => 1],
                                    'condition_type' => ['type' => 'integer', 'enum' => [1, 2], 'description' => '1 = all conditions true, 2 = any condition true.', 'example' => 1],
                                    'conditions' => ['type' => 'array', 'items' => ['type' => 'object'], 'description' => 'Condition rows: { attribute, operator, value, attribute_type }.', 'example' => [['attribute' => 'cart|base_sub_total', 'operator' => '>=', 'value' => '100', 'attribute_type' => 'price']]],
                                    'action_type' => ['type' => 'string', 'enum' => ['by_percent', 'by_fixed', 'cart_fixed', 'buy_x_get_y'], 'example' => 'by_percent'],
                                    'discount_amount' => ['type' => 'number', 'description' => '0-100 when action_type=by_percent.', 'example' => 10],
                                    'discount_quantity' => ['type' => 'integer', 'description' => 'Max quantity discounted (buy_x_get_y).', 'example' => 1],
                                    'discount_step' => ['type' => 'integer', 'description' => 'Buy-X step (buy_x_get_y).', 'example' => 0],
                                    'apply_to_shipping' => ['type' => 'integer', 'enum' => [0, 1], 'description' => 'Apply the discount to shipping too.', 'example' => 0],
                                    'free_shipping' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'end_other_rules' => ['type' => 'integer', 'enum' => [0, 1], 'description' => 'Stop processing further rules when this one matches.', 'example' => 0],
                                    'sort_order' => ['type' => 'integer', 'description' => 'Priority (lower runs first).', 'example' => 0],
                                    'starts_from' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-06-01 00:00:00'],
                                    'ends_till' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-08-31 23:59:59'],
                                    'status' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Cart rule created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 17,
                                    'name' => '10% off summer',
                                    'description' => 'Sitewide 10% off summer collection',
                                    'startsFrom' => '2026-06-01T00:00:00+05:30',
                                    'endsTill' => '2026-08-31T23:59:59+05:30',
                                    'status' => 1,
                                    'couponType' => 1,
                                    'useAutoGeneration' => 0,
                                    'usagePerCustomer' => 1,
                                    'usesPerCoupon' => 100,
                                    'timesUsed' => 0,
                                    'conditionType' => 1,
                                    'conditions' => [['attribute' => 'cart|base_sub_total', 'operator' => '>=', 'value' => '100', 'attribute_type' => 'price']],
                                    'actionType' => 'by_percent',
                                    'discountAmount' => 10,
                                    'discountQuantity' => 1,
                                    'discountStep' => '0',
                                    'applyToShipping' => 0,
                                    'freeShipping' => 0,
                                    'endOtherRules' => 0,
                                    'usesAttributeConditions' => 0,
                                    'sortOrder' => 0,
                                    'couponCode' => 'SUMMER10',
                                    'channels' => [['id' => 1, 'code' => 'default', 'name' => 'Default']],
                                    'customerGroups' => [['id' => 1, 'code' => 'guest', 'name' => 'Guest'], ['id' => 2, 'code' => 'general', 'name' => 'General']],
                                    'createdAt' => '2026-06-09T13:48:29+05:30',
                                    'updatedAt' => '2026-06-09T13:48:29+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/marketing/cart-rules/{id}',
            input: AdminMarketingCartRuleUpdateInput::class,
            output: AdminMarketingCartRuleRestDto::class,
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
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => '15% off summer'],
                                    'description' => ['type' => 'string', 'example' => 'Updated description'],
                                    'channels' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'customer_groups' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1, 2, 3]],
                                    'coupon_type' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'use_auto_generation' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'coupon_code' => ['type' => 'string', 'example' => 'SUMMER15'],
                                    'uses_per_coupon' => ['type' => 'integer', 'example' => 100],
                                    'usage_per_customer' => ['type' => 'integer', 'example' => 1],
                                    'condition_type' => ['type' => 'integer', 'enum' => [1, 2], 'example' => 1],
                                    'conditions' => ['type' => 'array', 'items' => ['type' => 'object'], 'example' => [['attribute' => 'cart|base_sub_total', 'operator' => '>=', 'value' => '100', 'attribute_type' => 'price']]],
                                    'action_type' => ['type' => 'string', 'enum' => ['by_percent', 'by_fixed', 'cart_fixed', 'buy_x_get_y'], 'example' => 'by_percent'],
                                    'discount_amount' => ['type' => 'number', 'example' => 15],
                                    'discount_quantity' => ['type' => 'integer', 'example' => 1],
                                    'discount_step' => ['type' => 'integer', 'example' => 0],
                                    'apply_to_shipping' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'free_shipping' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'end_other_rules' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'sort_order' => ['type' => 'integer', 'example' => 0],
                                    'starts_from' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-06-01 00:00:00'],
                                    'ends_till' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-08-31 23:59:59'],
                                    'status' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Cart rule updated; returns the updated detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 17,
                                    'name' => '15% off summer',
                                    'description' => 'Updated description',
                                    'startsFrom' => '2026-06-01T00:00:00+05:30',
                                    'endsTill' => '2026-08-31T23:59:59+05:30',
                                    'status' => 1,
                                    'couponType' => 1,
                                    'useAutoGeneration' => 0,
                                    'usagePerCustomer' => 1,
                                    'usesPerCoupon' => 100,
                                    'timesUsed' => 0,
                                    'conditionType' => 1,
                                    'conditions' => [['attribute' => 'cart|base_sub_total', 'operator' => '>=', 'value' => '100', 'attribute_type' => 'price']],
                                    'actionType' => 'by_percent',
                                    'discountAmount' => 15,
                                    'discountQuantity' => 1,
                                    'discountStep' => '0',
                                    'applyToShipping' => 0,
                                    'freeShipping' => 0,
                                    'endOtherRules' => 0,
                                    'usesAttributeConditions' => 0,
                                    'sortOrder' => 0,
                                    'couponCode' => 'SUMMER15',
                                    'channels' => [['id' => 1, 'code' => 'default', 'name' => 'Default']],
                                    'customerGroups' => [['id' => 1, 'code' => 'guest', 'name' => 'Guest'], ['id' => 2, 'code' => 'general', 'name' => 'General']],
                                    'createdAt' => '2026-06-09T13:48:29+05:30',
                                    'updatedAt' => '2026-06-10T09:20:11+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Cart rule not found.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                ],
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
                    '200' => new Model\Response(
                        description: 'Deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['message' => 'Cart rule deleted.'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/cart-rules/{id}',
            provider: AdminMarketingCartRuleItemProvider::class,
            output: AdminMarketingCartRuleRestDto::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Cart rule detail',
                parameters: [new Model\Parameter('id', 'path', 'Cart rule ID.', true, schema: ['type' => 'integer'])],
                responses: [
                    '200' => new Model\Response(
                        description: 'Single cart rule with conditions, channels and customerGroups (object arrays) resolved.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 17,
                                    'name' => '10% off summer',
                                    'description' => 'Sitewide 10% off summer collection',
                                    'startsFrom' => '2026-06-01T00:00:00+05:30',
                                    'endsTill' => '2026-08-31T23:59:59+05:30',
                                    'status' => 1,
                                    'couponType' => 1,
                                    'useAutoGeneration' => 0,
                                    'usagePerCustomer' => 1,
                                    'usesPerCoupon' => 100,
                                    'timesUsed' => 0,
                                    'conditionType' => 1,
                                    'conditions' => [['attribute' => 'cart|base_sub_total', 'operator' => '>=', 'value' => '100', 'attribute_type' => 'price']],
                                    'actionType' => 'by_percent',
                                    'discountAmount' => 10,
                                    'discountQuantity' => 1,
                                    'discountStep' => '0',
                                    'applyToShipping' => 0,
                                    'freeShipping' => 0,
                                    'endOtherRules' => 0,
                                    'usesAttributeConditions' => 0,
                                    'sortOrder' => 0,
                                    'couponCode' => 'SUMMER10',
                                    'channels' => [['id' => 1, 'code' => 'default', 'name' => 'Default']],
                                    'customerGroups' => [['id' => 2, 'code' => 'general', 'name' => 'General']],
                                    'createdAt' => '2026-06-09T13:48:29+05:30',
                                    'updatedAt' => '2026-06-09T13:48:29+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Cart rule not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/marketing/cart-rules/{id}/copy',
            requirements: ['id' => '\d+'],
            input: AdminMarketingCartRuleCopyInput::class,
            output: AdminMarketingCartRuleRestDto::class,
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
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Cart rule copied; returns the new rule detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 18,
                                    'name' => 'Copy of 10% off summer',
                                    'description' => 'Sitewide 10% off summer collection',
                                    'startsFrom' => '2026-06-01T00:00:00+05:30',
                                    'endsTill' => '2026-08-31T23:59:59+05:30',
                                    'status' => 0,
                                    'couponType' => 1,
                                    'useAutoGeneration' => 0,
                                    'usagePerCustomer' => 1,
                                    'usesPerCoupon' => 100,
                                    'timesUsed' => 0,
                                    'conditionType' => 1,
                                    'conditions' => [['attribute' => 'cart|base_sub_total', 'operator' => '>=', 'value' => '100', 'attribute_type' => 'price']],
                                    'actionType' => 'by_percent',
                                    'discountAmount' => 10,
                                    'discountQuantity' => 1,
                                    'discountStep' => '0',
                                    'applyToShipping' => 0,
                                    'freeShipping' => 0,
                                    'endOtherRules' => 0,
                                    'usesAttributeConditions' => 0,
                                    'sortOrder' => 0,
                                    'couponCode' => null,
                                    'channels' => [['id' => 1, 'code' => 'default', 'name' => 'Default']],
                                    'customerGroups' => [['id' => 1, 'code' => 'guest', 'name' => 'Guest'], ['id' => 2, 'code' => 'general', 'name' => 'General']],
                                    'createdAt' => '2026-06-10T11:05:00+05:30',
                                    'updatedAt' => '2026-06-10T11:05:00+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks marketing.promotions.cart_rules.create.'),
                    '404' => new Model\Response(description: 'Source cart rule not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/cart-rules',
            provider: AdminMarketingCartRuleCollectionProvider::class,
            output: AdminMarketingCartRuleRestDto::class,
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
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list in the { data, meta } envelope. conditions / channels / customerGroups are detail-only and null on list rows.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 17,
                                            'name' => '10% off summer',
                                            'description' => 'Sitewide 10% off summer collection',
                                            'startsFrom' => '2026-06-01T00:00:00+05:30',
                                            'endsTill' => '2026-08-31T23:59:59+05:30',
                                            'status' => 1,
                                            'couponType' => 1,
                                            'useAutoGeneration' => 0,
                                            'usagePerCustomer' => 1,
                                            'usesPerCoupon' => 100,
                                            'timesUsed' => 0,
                                            'conditionType' => 1,
                                            'conditions' => null,
                                            'actionType' => 'by_percent',
                                            'discountAmount' => 10,
                                            'discountQuantity' => 1,
                                            'discountStep' => '0',
                                            'applyToShipping' => 0,
                                            'freeShipping' => 0,
                                            'endOtherRules' => 0,
                                            'usesAttributeConditions' => 0,
                                            'sortOrder' => 0,
                                            'couponCode' => 'SUMMER10',
                                            'channels' => null,
                                            'customerGroups' => null,
                                            'createdAt' => '2026-06-09T13:48:29+05:30',
                                            'updatedAt' => '2026-06-09T13:48:29+05:30',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 1,
                                        'total' => 1,
                                        'from' => 1,
                                        'to' => 1,
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
            provider: AdminMarketingCartRuleCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'id' => ['type' => 'String'],
                'name' => ['type' => 'String'],
                'coupon_code' => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'coupon_type' => ['type' => 'Int'],
                'sort_order' => ['type' => 'Int'],
                'starts_from_from' => ['type' => 'String'],
                'starts_from_to' => ['type' => 'String'],
                'ends_till_from' => ['type' => 'String'],
                'ends_till_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
            description: 'Admin marketing cart rules listing (cursor pagination). channels / customerGroups connections are detail-only (empty on list rows).',
        ),
        new Query(
            provider: AdminMarketingCartRuleItemProvider::class,
            description: 'Admin marketing cart rule detail by id. Sub-select channels { edges { node } } and customerGroups { edges { node } }.',
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
class AdminMarketingCartRule extends EloquentModel
{
    /** @var string */
    protected $table = 'cart_rules';

    /** @var array */
    protected $appends = ['coupon_code'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'status' => 'int',
        'coupon_type' => 'int',
        'use_auto_generation' => 'int',
        'usage_per_customer' => 'int',
        'uses_per_coupon' => 'int',
        'times_used' => 'int',
        'condition_type' => 'int',
        'apply_to_shipping' => 'int',
        'free_shipping' => 'int',
        'end_other_rules' => 'int',
        'uses_attribute_conditions' => 'int',
        'sort_order' => 'int',
        'discount_amount' => 'float',
        'discount_quantity' => 'float',
        'conditions' => 'array',
        'starts_from' => 'datetime',
        'ends_till' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    /**
     * Primary coupon code — a STRING accessor (safe over GraphQL) recomputed from
     * the row's own id, so it resolves on the Eloquent parent (the listing
     * forceFills it as a no-N+1 fast-path).
     */
    #[ApiProperty(writable: false)]
    public function getCouponCodeAttribute(): ?string
    {
        if (array_key_exists('coupon_code', $this->attributes)) {
            return $this->attributes['coupon_code'];
        }

        if ($this->id === null) {
            return null;
        }

        return DB::table('cart_rule_coupons')
            ->where('cart_rule_id', $this->id)
            ->where('is_primary', 1)
            ->value('code');
    }

    /**
     * Assigned channels (GraphQL connection — `channels { edges }`). belongsToMany
     * over `cart_rule_channels` — the pivot has no own id, so the node `_id` is the
     * channel's real id.
     */
    #[ApiProperty(writable: false)]
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminMarketingChannelRef::class,
            'cart_rule_channels',
            'cart_rule_id',
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
            'cart_rule_customer_groups',
            'cart_rule_id',
            'customer_group_id',
        );
    }
}
