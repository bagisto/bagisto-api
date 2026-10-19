<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsDataTransferImportActionInput
{
    /**
     * The import id.
     */
    #[ApiProperty(description: 'ID of the import to act on.')]
    #[Groups(['mutation'])]
    public ?int $importId = null;
}
