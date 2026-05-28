<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/tax-rates/{id} and the GraphQL
 * update/delete mutations (delete reuses the same input — only `id` is required).
 */
class AdminSettingsTaxRateUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/tax-rates/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $identifier = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?bool $is_zip = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $zip_code = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $zip_from = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $zip_to = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $state = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $country = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $tax_rate = null;
}
