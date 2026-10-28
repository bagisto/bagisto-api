<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsLocaleMassDeleteInput
{
    /**
     * The ids of the locales to delete.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of locale IDs to delete.')]
    #[Groups(['mutation'])]
    public ?array $indices = null;
}
