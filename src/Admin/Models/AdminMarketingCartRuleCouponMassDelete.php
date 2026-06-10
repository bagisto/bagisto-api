<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCartRuleCouponMassDeleteInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingCartRuleCouponMassDeleteProcessor;

/**
 * Mass-delete cart rule coupons (Block F1c).
 *
 * REST: POST /api/admin/marketing/cart-rules/{cartRuleId}/coupons/mass-delete
 * GraphQL: createAdminMarketingCartRuleCouponMassDelete
 *
 * Body: { indices: int[] }. IDs not belonging to {cartRuleId} are silently
 * skipped (cross-rule isolation). Fires cart_rules.coupons.delete.{before,after}
 * per id, mirroring the monolith.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingCartRuleCouponMassDelete',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/marketing/cart-rules/{cartRuleId}/coupons/mass-delete',
            input: AdminMarketingCartRuleCouponMassDeleteInput::class,
            processor: AdminMarketingCartRuleCouponMassDeleteProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Promotions'],
                summary: 'Mass-delete coupons for a cart rule',
                parameters: [
                    new Model\Parameter('cartRuleId', 'path', 'Parent cart rule ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['indices'],
                                'properties' => [
                                    'indices' => [
                                        'type'    => 'array',
                                        'items'   => ['type' => 'integer'],
                                        'example' => [12, 13, 14],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminMarketingCartRuleCouponMassDeleteInput::class,
            processor: AdminMarketingCartRuleCouponMassDeleteProcessor::class,
            description: 'Mass-delete coupons for a cart rule. Becomes createAdminMarketingCartRuleCouponMassDelete.',
        ),
    ],
)]
class AdminMarketingCartRuleCouponMassDelete
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $cartRuleId = null;

    #[ApiProperty(writable: false)]
    public ?int $deleted = null;

    /** @var array<int, int>|null */
    #[ApiProperty(writable: false)]
    public ?array $skipped = null;

    #[ApiProperty(writable: false)]
    public ?bool $success = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
