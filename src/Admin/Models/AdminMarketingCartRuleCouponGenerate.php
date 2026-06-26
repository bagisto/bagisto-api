<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCouponGenerateInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponGenerateProcessor;

/**
 * Bulk-generate cart rule coupons (Block F1c).
 *
 * Mirrors CartRuleCouponController::store + CartRuleCouponRepository::generateCoupons.
 *
 * REST: POST /api/admin/marketing/cart-rules/{cartRuleId}/coupons/generate
 * GraphQL: createAdminMarketingCartRuleCouponGenerate
 *
 * Body (REST and GraphQL accept both core's keys and the spec's friendlier names):
 *   - length     | code_length   integer 4-30
 *   - format     | code_format   string  alphabetic|alphanumeric|numeric
 *                                        (also accepted: alphabetical — core's spelling)
 *   - prefix     | code_prefix   string  optional
 *   - suffix     | code_suffix   string  optional
 *   - coupon_qty                 integer 1-100
 *
 * Inherits usage_limit / usage_per_customer / expired_at from the parent
 * cart_rule (uses_per_coupon, usage_per_customer, ends_till) — see
 * CartRuleCouponRepository::generateCoupons.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCartRuleCouponGenerate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/cart-rules/{cartRuleId}/coupons/generate',
            input: AdminMarketingCartRuleCouponGenerateInput::class,
            processor: AdminMarketingCartRuleCouponGenerateProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Bulk-generate coupons for a cart rule',
                description: 'Generates `coupon_qty` random codes of the given format and length, optionally with prefix/suffix. Inherits usage_limit/usage_per_customer/expired_at from the parent cart rule.',
                parameters: [
                    new Model\Parameter('cartRuleId', 'path', 'Parent cart rule ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['length', 'format', 'coupon_qty'],
                                'properties' => [
                                    'length'     => ['type' => 'integer', 'minimum' => 4, 'maximum' => 30, 'example' => 10],
                                    'format'     => ['type' => 'string', 'enum' => ['alphabetic', 'alphanumeric', 'numeric'], 'example' => 'alphanumeric'],
                                    'prefix'     => ['type' => 'string', 'nullable' => true, 'example' => 'SAVE-'],
                                    'suffix'     => ['type' => 'string', 'nullable' => true, 'example' => '-2026'],
                                    'coupon_qty' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'example' => 5],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Coupons generated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'generated' => 3,
                                    'coupons'   => [
                                        [
                                            'id'               => 23,
                                            'cartRuleId'       => 47,
                                            'code'             => 'SUMMER-AB12',
                                            'usageLimit'       => 100,
                                            'usagePerCustomer' => 1,
                                            'timesUsed'        => 0,
                                            'type'             => 1,
                                            'isPrimary'        => false,
                                            'expiredAt'        => '2026-12-31',
                                            'createdAt'        => '2026-06-10T09:00:00+05:30',
                                            'updatedAt'        => '2026-06-10T09:00:00+05:30',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Parent cart rule not found.'),
                    '422' => new Model\Response(description: 'Validation failed (invalid length, format, or coupon_qty).'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminMarketingCartRuleCouponGenerateInput::class,
            processor: AdminMarketingCartRuleCouponGenerateProcessor::class,
            description: 'Bulk-generate coupons for a cart rule. Becomes createAdminMarketingCartRuleCouponGenerate.',
        ),
    ],
)]
class AdminMarketingCartRuleCouponGenerate
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $cartRuleId = null;

    #[ApiProperty(writable: false)]
    public ?int $generated = null;

    /** @var array<int, array<string, mixed>>|null */
    #[ApiProperty(writable: false)]
    public ?array $coupons = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
