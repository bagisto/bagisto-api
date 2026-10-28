<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCartRuleCouponDeleteInput
{
    /**
     * The id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The cart rule id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $cartRuleId = null;
}
