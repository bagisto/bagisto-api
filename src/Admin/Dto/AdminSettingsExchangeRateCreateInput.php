<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsExchangeRateCreateInput
{
    /**
     * The target currency.
     */
    #[ApiProperty(description: 'Target currency ID. Must exist in currencies and be unique among existing exchange rates.')]
    #[Groups(['mutation'])]
    public ?int $target_currency = null;

    /**
     * The rate.
     */
    #[ApiProperty(description: 'Exchange rate (positive float).')]
    #[Groups(['mutation'])]
    public ?float $rate = null;
}
