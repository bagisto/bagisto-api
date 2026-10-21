<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminMarketingCartRuleMassDeleteInput
{
    /**
     * The ids of the cart rules to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of cart rule IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
