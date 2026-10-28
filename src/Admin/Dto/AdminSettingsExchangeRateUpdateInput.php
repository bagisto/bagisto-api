<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsExchangeRateUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/exchange-rates/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The target currency.
     */
    #[ApiProperty(description: 'Target currency ID. Must exist in currencies and be unique (excluding self).')]
    #[Groups(['mutation'])]
    public ?int $target_currency = null;

    /**
     * The rate.
     */
    #[ApiProperty(description: 'Exchange rate (positive float).')]
    #[Groups(['mutation'])]
    public ?float $rate = null;
}
