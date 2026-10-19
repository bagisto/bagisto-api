<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsDataTransferImportCancelInput
{
    /**
     * The import id.
     */
    #[ApiProperty(description: 'ID of the import to cancel.')]
    #[Groups(['mutation'])]
    public ?int $importId = null;
}
