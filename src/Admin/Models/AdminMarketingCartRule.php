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
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCollectionProvider;
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
                tags: ['Admin Marketing'],
                summary: 'Create a new cart rule',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['name', 'channels', 'customer_groups', 'coupon_type', 'action_type', 'discount_amount'],
                                'properties' => [
                                    'name'            => ['type' => 'string', 'example' => '10% off summer'],
                                    'description'     => ['type' => 'string'],
                                    'channels'        => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'customer_groups' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1, 2, 3]],
                                    'starts_from'     => ['type' => 'string', 'format' => 'date-time'],
                                    'ends_till'       => ['type' => 'string', 'format' => 'date-time'],
                                    'status'          => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'coupon_type'     => ['type' => 'integer', 'enum' => [1, 2], 'example' => 1],
                                    'action_type'     => ['type' => 'string', 'enum' => ['by_percent', 'by_fixed', 'cart_fixed', 'buy_x_get_y'], 'example' => 'by_percent'],
                                    'discount_amount' => ['type' => 'number', 'example' => 10],
                                    'condition_type'  => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'conditions'      => ['type' => 'array'],
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
                tags: ['Admin Marketing'],
                summary: 'Update a cart rule',
                parameters: [new Model\Parameter('id', 'path', 'Cart rule ID.', true, schema: ['type' => 'integer'])],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/cart-rules/{id}',
            provider: AdminMarketingCartRuleWriteProvider::class,
            processor: AdminMarketingCartRuleProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
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
                tags: ['Admin Marketing'],
                summary: 'Cart rule detail',
                parameters: [new Model\Parameter('id', 'path', 'Cart rule ID.', true, schema: ['type' => 'integer'])],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/cart-rules',
            provider: AdminMarketingCartRuleCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing'],
                summary: 'List cart rules',
                description: 'Paginated, filterable, sortable list. Returns the standard { data, meta } admin envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('name', 'query', 'Filter by name (partial match).', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Filter by status (0/1).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('coupon_type', 'query', 'Filter by coupon_type (1/2).', false, schema: ['type' => 'integer']),
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
                'name'        => ['type' => 'String'],
                'status'      => ['type' => 'Int'],
                'coupon_type' => ['type' => 'Int'],
                'sort'        => ['type' => 'String'],
                'order'       => ['type' => 'String'],
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
    ],
)]
class AdminMarketingCartRule
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $startsFrom = null;

    #[ApiProperty(writable: false)]
    public ?string $endsTill = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?int $couponType = null;

    #[ApiProperty(writable: false)]
    public ?int $useAutoGeneration = null;

    #[ApiProperty(writable: false)]
    public ?int $usagePerCustomer = null;

    #[ApiProperty(writable: false)]
    public ?int $usesPerCoupon = null;

    #[ApiProperty(writable: false)]
    public ?int $timesUsed = null;

    #[ApiProperty(writable: false)]
    public ?int $conditionType = null;

    /** @var array<int,mixed>|null */
    #[ApiProperty(writable: false)]
    public ?array $conditions = null;

    #[ApiProperty(writable: false)]
    public ?string $actionType = null;

    #[ApiProperty(writable: false)]
    public ?float $discountAmount = null;

    #[ApiProperty(writable: false)]
    public ?int $discountQuantity = null;

    #[ApiProperty(writable: false)]
    public ?string $discountStep = null;

    #[ApiProperty(writable: false)]
    public ?int $applyToShipping = null;

    #[ApiProperty(writable: false)]
    public ?int $freeShipping = null;

    #[ApiProperty(writable: false)]
    public ?int $endOtherRules = null;

    #[ApiProperty(writable: false)]
    public ?int $usesAttributeConditions = null;

    #[ApiProperty(writable: false)]
    public ?int $sortOrder = null;

    #[ApiProperty(writable: false)]
    public ?string $couponCode = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $channels = null;

    /** @var int[]|null */
    #[ApiProperty(writable: false)]
    public ?array $customerGroups = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;
}
