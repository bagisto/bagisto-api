<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCartRuleCopyInput
{
    /**
     * The cart rule id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $cartRuleId = null;
}
