<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/tax-rates.
 *
 * Mirrors Bagisto admin TaxRateRequest validation:
 *   - identifier: required, unique on tax_rates.identifier
 *   - country: required (2-letter)
 *   - tax_rate: required, numeric, 0..100
 *   - is_zip: required boolean — when true the (zip_from, zip_to) range is used,
 *     when false zip_code is used. Conditional rules apply.
 */
class AdminSettingsTaxRateCreateInput
{
    #[ApiProperty(description: 'Unique label for the rate. Required.')]
    #[Groups(['mutation'])]
    public ?string $identifier = null;

    #[ApiProperty(description: 'Whether the rate uses a zip range (true) or a specific zip code (false).')]
    #[Groups(['mutation'])]
    public ?bool $is_zip = null;

    #[ApiProperty(description: 'Specific zip code. Required when is_zip is false.')]
    #[Groups(['mutation'])]
    public ?string $zip_code = null;

    #[ApiProperty(description: 'Start of zip range. Required when is_zip is true.')]
    #[Groups(['mutation'])]
    public ?string $zip_from = null;

    #[ApiProperty(description: 'End of zip range. Required when is_zip is true.')]
    #[Groups(['mutation'])]
    public ?string $zip_to = null;

    #[ApiProperty(description: 'State code (optional).')]
    #[Groups(['mutation'])]
    public ?string $state = null;

    #[ApiProperty(description: 'Country code (ISO 2-letter). Required.')]
    #[Groups(['mutation'])]
    public ?string $country = null;

    #[ApiProperty(description: 'Tax rate percentage (0..100). Required.')]
    #[Groups(['mutation'])]
    public ?float $tax_rate = null;
}
