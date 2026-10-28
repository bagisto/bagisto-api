<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsTaxRateCreateInput
{
    /**
     * The identifier.
     */
    #[ApiProperty(description: 'Unique label for the rate. Required.')]
    #[Groups(['mutation'])]
    public ?string $identifier = null;

    /**
     * The is zip.
     */
    #[ApiProperty(description: 'Whether the rate uses a zip range (true) or a specific zip code (false).')]
    #[Groups(['mutation'])]
    public ?bool $is_zip = null;

    /**
     * The zip code.
     */
    #[ApiProperty(description: 'Specific zip code. Required when is_zip is false.')]
    #[Groups(['mutation'])]
    public ?string $zip_code = null;

    /**
     * The zip from.
     */
    #[ApiProperty(description: 'Start of zip range. Required when is_zip is true.')]
    #[Groups(['mutation'])]
    public ?string $zip_from = null;

    /**
     * The zip to.
     */
    #[ApiProperty(description: 'End of zip range. Required when is_zip is true.')]
    #[Groups(['mutation'])]
    public ?string $zip_to = null;

    /**
     * The state.
     */
    #[ApiProperty(description: 'State code (optional).')]
    #[Groups(['mutation'])]
    public ?string $state = null;

    /**
     * The country.
     */
    #[ApiProperty(description: 'Country code (ISO 2-letter). Required.')]
    #[Groups(['mutation'])]
    public ?string $country = null;

    /**
     * The tax rate.
     */
    #[ApiProperty(description: 'Tax rate percentage (0..100). Required.')]
    #[Groups(['mutation'])]
    public ?float $tax_rate = null;
}
