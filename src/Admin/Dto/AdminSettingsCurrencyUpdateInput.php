<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsCurrencyUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/currencies/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The symbol.
     */
    #[ApiProperty(description: 'Optional symbol.')]
    #[Groups(['mutation'])]
    public ?string $symbol = null;

    /**
     * The decimal.
     */
    #[ApiProperty(description: 'Number of decimal places.')]
    #[Groups(['mutation'])]
    public ?int $decimal = null;

    /**
     * The group separator.
     */
    #[ApiProperty(description: 'Thousands group separator.')]
    #[Groups(['mutation'])]
    public ?string $group_separator = null;

    /**
     * The decimal separator.
     */
    #[ApiProperty(description: 'Decimal separator.')]
    #[Groups(['mutation'])]
    public ?string $decimal_separator = null;

    /**
     * The currency position.
     */
    #[ApiProperty(description: 'Symbol position.')]
    #[Groups(['mutation'])]
    public ?string $currency_position = null;
}
