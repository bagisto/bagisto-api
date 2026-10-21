<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCartRuleCouponMassDeleteInput
{
    /**
     * The cart rule id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $cartRuleId = null;

    /**
     * The ids of the cart rule coupons to delete.
     *
     * @var array<int, int>|null
     */
    #[ApiProperty(description: 'Coupon IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
