<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCartRuleCouponGenerateInput
{
    /**
     * The cart rule id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $cartRuleId = null;

    /**
     * The length.
     */
    #[ApiProperty(description: 'Code length between 4 and 30.')]
    #[Groups(['mutation'])]
    public ?int $length = null;

    /**
     * The format.
     */
    #[ApiProperty(description: 'One of: alphabetic, alphanumeric, numeric.')]
    #[Groups(['mutation'])]
    public ?string $format = null;

    /**
     * The prefix.
     */
    #[ApiProperty(description: 'Optional prefix.')]
    #[Groups(['mutation'])]
    public ?string $prefix = null;

    /**
     * The suffix.
     */
    #[ApiProperty(description: 'Optional suffix.')]
    #[Groups(['mutation'])]
    public ?string $suffix = null;

    /**
     * The coupon qty.
     */
    #[ApiProperty(description: 'How many codes to generate (1-100).')]
    #[Groups(['mutation'])]
    public ?int $couponQty = null;
}
