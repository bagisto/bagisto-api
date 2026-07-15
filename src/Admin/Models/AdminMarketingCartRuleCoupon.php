<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCouponCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCouponDeleteInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponWriteProvider;

/**
 * Admin Marketing — Cart Rule Coupons sub-resource (Block F1c).
 *
 * Sub-resource under cart rules: /api/admin/marketing/cart-rules/{cartRuleId}/coupons.
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Promotions\CartRuleCouponController.
 *
 * REST:
 *   GET    /api/admin/marketing/cart-rules/{cartRuleId}/coupons
 *   POST   /api/admin/marketing/cart-rules/{cartRuleId}/coupons         (single create)
 *   DELETE /api/admin/marketing/cart-rules/{cartRuleId}/coupons/{id}
 *
 * Bulk generate + mass-delete are separate top-level resources
 * (AdminMarketingCartRuleCouponGenerate / AdminMarketingCartRuleCouponMassDelete).
 *
 * Permissions:
 *   - list  → marketing.promotions.cart_rules.view  (falls back to *.create when missing)
 *   - create/generate → marketing.promotions.cart_rules.create
 *   - delete → marketing.promotions.cart_rules.delete
 *
 * Ownership: every coupon read/written through these endpoints must belong to
 * the cart rule named in the URL (`cart_rule_id` match). Cross-rule access → 404.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCartRuleCoupon',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/marketing/cart-rules/{cartRuleId}/coupons',
            provider: AdminMarketingCartRuleCouponCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'List coupons for a cart rule',
                parameters: [
                    new Model\Parameter('cartRuleId', 'path', 'Parent cart rule ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Coupons for the cart rule.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 22,
                                            'cartRuleId' => 47,
                                            'code' => 'SUMMER10',
                                            'usageLimit' => 100,
                                            'usagePerCustomer' => 1,
                                            'timesUsed' => 0,
                                            'type' => 1,
                                            'isPrimary' => false,
                                            'expiredAt' => '2026-12-31',
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
                    '404' => new Model\Response(description: 'Cart rule not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/marketing/cart-rules/{cartRuleId}/coupons',
            input: AdminMarketingCartRuleCouponCreateInput::class,
            processor: AdminMarketingCartRuleCouponProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Create a single coupon code for a cart rule',
                parameters: [
                    new Model\Parameter('cartRuleId', 'path', 'Parent cart rule ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code'],
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'WELCOME10'],
                                    'usage_limit' => ['type' => 'integer', 'example' => 100, 'nullable' => true],
                                    'usage_per_customer' => ['type' => 'integer', 'example' => 1, 'nullable' => true],
                                    'expired_at' => ['type' => 'string', 'format' => 'date', 'nullable' => true, 'example' => '2027-12-31'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Coupon created.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 22,
                                    'cartRuleId' => 47,
                                    'code' => 'SUMMER10',
                                    'usageLimit' => 100,
                                    'usagePerCustomer' => 1,
                                    'timesUsed' => 0,
                                    'type' => 1,
                                    'isPrimary' => false,
                                    'expiredAt' => '2026-12-31',
                                    'createdAt' => '2026-06-09T13:48:29+05:30',
                                    'updatedAt' => '2026-06-09T13:48:29+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Cart rule not found.'),
                    '422' => new Model\Response(description: 'Validation failed (e.g. code missing or already in use).'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/cart-rules/{cartRuleId}/coupons/{id}',
            provider: AdminMarketingCartRuleCouponWriteProvider::class,
            processor: AdminMarketingCartRuleCouponProcessor::class,
            requirements: ['cartRuleId' => '\d+', 'id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Delete a coupon',
                parameters: [
                    new Model\Parameter('cartRuleId', 'path', 'Parent cart rule ID', true, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'path', 'Coupon ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Coupon deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'message' => 'Coupon deleted.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Coupon not found, or it does not belong to this cart rule.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminMarketingCartRuleCouponCollectionProvider::class,
            paginationType: 'cursor',
            description: 'List coupons for a cart rule.',
            extraArgs: [
                'cartRuleId' => ['type' => 'Int!', 'description' => 'Parent cart rule ID'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminMarketingCartRuleCouponCreateInput::class,
            processor: AdminMarketingCartRuleCouponProcessor::class,
            description: 'Create a single coupon for a cart rule. Becomes createAdminMarketingCartRuleCoupon.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingCartRuleCouponDeleteInput::class,
            processor: AdminMarketingCartRuleCouponProcessor::class,
            description: 'Delete a coupon. Becomes deleteAdminMarketingCartRuleCoupon.',
        ),
    ],
)]
class AdminMarketingCartRuleCoupon
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $cart_rule_id = null;

    #[ApiProperty(writable: false)]
    public ?string $code = null;

    #[ApiProperty(writable: false)]
    public ?int $usage_limit = null;

    #[ApiProperty(writable: false)]
    public ?int $usage_per_customer = null;

    #[ApiProperty(writable: false)]
    public ?int $times_used = null;

    #[ApiProperty(writable: false)]
    public ?int $type = null;

    #[ApiProperty(writable: false)]
    public ?bool $is_primary = null;

    #[ApiProperty(writable: false)]
    public ?string $expired_at = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
