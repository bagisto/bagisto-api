<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Bulk-generate input. Accepts both core's keys (code_length / code_format /
 * code_prefix / code_suffix) AND the spec's friendlier names (length / format
 * / prefix / suffix). The processor's normalize() resolves either shape.
 */
class AdminMarketingCartRuleCouponGenerateInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $cartRuleId = null;

    #[ApiProperty(description: 'Code length between 4 and 30.')]
    #[Groups(['mutation'])]
    public ?int $length = null;

    #[ApiProperty(description: 'One of: alphabetic, alphanumeric, numeric.')]
    #[Groups(['mutation'])]
    public ?string $format = null;

    #[ApiProperty(description: 'Optional prefix.')]
    #[Groups(['mutation'])]
    public ?string $prefix = null;

    #[ApiProperty(description: 'Optional suffix.')]
    #[Groups(['mutation'])]
    public ?string $suffix = null;

    #[ApiProperty(description: 'How many codes to generate (1-100).')]
    #[Groups(['mutation'])]
    public ?int $couponQty = null;
}
