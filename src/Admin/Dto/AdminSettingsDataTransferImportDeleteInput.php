<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsDataTransferImportDeleteInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/data-transfer/imports/3).')]
    #[Groups(['mutation'])]
    public ?string $id = null;
}
