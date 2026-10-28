<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsExchangeRateMassDeleteInput
{
    /**
     * The ids of the exchange rates to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of exchange-rate IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
