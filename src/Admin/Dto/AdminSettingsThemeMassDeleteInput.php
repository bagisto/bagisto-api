<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsThemeMassDeleteInput
{
    /**
     * The ids of the theme customizations to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of theme customization IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
