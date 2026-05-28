<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/exchange-rates.
 *
 * Mirrors Bagisto admin ExchangeRateController::store validation:
 *   - target_currency: required, must exist in currencies, unique in currency_exchange_rates
 *   - rate: required, numeric, > 0
 */
class AdminSettingsExchangeRateCreateInput
{
    #[ApiProperty(description: 'Target currency ID. Must exist in currencies and be unique among existing exchange rates.')]
    #[Groups(['mutation'])]
    public ?int $target_currency = null;

    #[ApiProperty(description: 'Exchange rate (positive float).')]
    #[Groups(['mutation'])]
    public ?float $rate = null;
}
