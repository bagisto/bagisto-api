<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/exchange-rates/{id} and the GraphQL
 * update/delete mutations (delete reuses the same input — only `id` is required).
 */
class AdminSettingsExchangeRateUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/exchange-rates/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Target currency ID. Must exist in currencies and be unique (excluding self).')]
    #[Groups(['mutation'])]
    public ?int $target_currency = null;

    #[ApiProperty(description: 'Exchange rate (positive float).')]
    #[Groups(['mutation'])]
    public ?float $rate = null;
}
