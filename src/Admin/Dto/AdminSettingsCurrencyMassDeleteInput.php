<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsCurrencyMassDeleteInput
{
    /**
     * The ids of the currencies to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of currency IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
