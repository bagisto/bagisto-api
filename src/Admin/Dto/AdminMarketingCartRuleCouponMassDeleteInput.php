<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCartRuleCouponMassDeleteInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $cartRuleId = null;

    /** @var array<int, int>|null */
    #[ApiProperty(description: 'Coupon IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
