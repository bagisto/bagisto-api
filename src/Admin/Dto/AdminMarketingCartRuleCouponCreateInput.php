<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Create input for a single cart-rule coupon code.
 */
class AdminMarketingCartRuleCouponCreateInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $cartRuleId = null;

    #[ApiProperty(description: 'Coupon code (unique).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Maximum total uses (null = inherit from parent rule).')]
    #[Groups(['mutation'])]
    public ?int $usageLimit = null;

    #[ApiProperty(description: 'Maximum uses per customer.')]
    #[Groups(['mutation'])]
    public ?int $usagePerCustomer = null;

    #[ApiProperty(description: 'Expiry date in YYYY-MM-DD format.')]
    #[Groups(['mutation'])]
    public ?string $expiredAt = null;
}
