<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/currencies.
 *
 * Mirrors Bagisto core CurrencyController::store validation:
 *   - code: required, exactly 3 chars, alpha, unique on currencies.code
 *   - name: required
 */
class AdminSettingsCurrencyCreateInput
{
    #[ApiProperty(description: 'ISO-4217 alpha-3 currency code (e.g. USD, EUR). Uppercased automatically.')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Optional symbol (e.g. $, €). Falls back to the code-based default.')]
    #[Groups(['mutation'])]
    public ?string $symbol = null;

    #[ApiProperty(description: 'Number of decimal places. Defaults to 2.')]
    #[Groups(['mutation'])]
    public ?int $decimal = null;

    #[ApiProperty(description: 'Thousands group separator (e.g. ",").')]
    #[Groups(['mutation'])]
    public ?string $group_separator = null;

    #[ApiProperty(description: 'Decimal separator (e.g. ".").')]
    #[Groups(['mutation'])]
    public ?string $decimal_separator = null;

    #[ApiProperty(description: 'Symbol position (one of left, right, left_with_space, right_with_space).')]
    #[Groups(['mutation'])]
    public ?string $currency_position = null;
}
