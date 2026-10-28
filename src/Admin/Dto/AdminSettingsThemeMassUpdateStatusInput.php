<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsThemeMassUpdateStatusInput
{
    /**
     * The ids of the theme customizations whose status is updated.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of theme customization IDs.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;

    /**
     * The value.
     */
    #[ApiProperty(description: 'New status value (0 or 1).')]
    #[Groups(['mutation'])]
    public ?int $value = null;
}
