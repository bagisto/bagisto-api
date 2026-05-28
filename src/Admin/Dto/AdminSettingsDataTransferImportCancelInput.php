<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for cancelAdminSettingsDataTransferImport (GraphQL) /
 * POST /api/admin/settings/data-transfer/imports/{id}/cancel (REST).
 *
 * REST reads the import id from the URI; GraphQL reads it from this DTO.
 */
class AdminSettingsDataTransferImportCancelInput
{
    #[ApiProperty(description: 'ID of the import to cancel.')]
    #[Groups(['mutation'])]
    public ?int $importId = null;
}
