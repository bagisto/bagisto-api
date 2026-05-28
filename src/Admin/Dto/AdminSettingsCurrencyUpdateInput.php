<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/currencies/{id} and the delete mutation.
 *
 * Update validation: name is required. The `code` field is immutable (the monolith
 * controller does not include it in the update payload).
 */
class AdminSettingsCurrencyUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/currencies/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Optional symbol.')]
    #[Groups(['mutation'])]
    public ?string $symbol = null;

    #[ApiProperty(description: 'Number of decimal places.')]
    #[Groups(['mutation'])]
    public ?int $decimal = null;

    #[ApiProperty(description: 'Thousands group separator.')]
    #[Groups(['mutation'])]
    public ?string $group_separator = null;

    #[ApiProperty(description: 'Decimal separator.')]
    #[Groups(['mutation'])]
    public ?string $decimal_separator = null;

    #[ApiProperty(description: 'Symbol position.')]
    #[Groups(['mutation'])]
    public ?string $currency_position = null;
}
