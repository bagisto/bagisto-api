<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for deleteAdminSettingsDataTransferImport GraphQL mutation.
 *
 * GraphQL mutations require an input type with the resource IRI as `id`. The
 * processor extracts the numeric id via basename().
 */
class AdminSettingsDataTransferImportDeleteInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/data-transfer/imports/3).')]
    #[Groups(['mutation'])]
    public ?string $id = null;
}
