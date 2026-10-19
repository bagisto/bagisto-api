<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsTaxRateUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/tax-rates/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The identifier.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $identifier = null;

    /**
     * The is zip.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?bool $is_zip = null;

    /**
     * The zip code.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $zip_code = null;

    /**
     * The zip from.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $zip_from = null;

    /**
     * The zip to.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $zip_to = null;

    /**
     * The state.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $state = null;

    /**
     * The country.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $country = null;

    /**
     * The tax rate.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $tax_rate = null;
}
