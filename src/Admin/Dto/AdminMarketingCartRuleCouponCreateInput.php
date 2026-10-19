<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCartRuleCouponCreateInput
{
    /**
     * The cart rule id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $cartRuleId = null;

    /**
     * The code.
     */
    #[ApiProperty(description: 'Coupon code (unique).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The usage limit.
     */
    #[ApiProperty(description: 'Maximum total uses (null = inherit from parent rule).')]
    #[Groups(['mutation'])]
    public ?int $usageLimit = null;

    /**
     * The usage per customer.
     */
    #[ApiProperty(description: 'Maximum uses per customer.')]
    #[Groups(['mutation'])]
    public ?int $usagePerCustomer = null;

    /**
     * The expired at.
     */
    #[ApiProperty(description: 'Expiry date in YYYY-MM-DD format.')]
    #[Groups(['mutation'])]
    public ?string $expiredAt = null;
}
