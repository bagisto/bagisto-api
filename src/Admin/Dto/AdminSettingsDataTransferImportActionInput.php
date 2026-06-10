<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Shared input DTO for the import pipeline action mutations
 * (validate / start / link / index). REST reads the import id from the URI;
 * GraphQL reads it from this DTO.
 */
class AdminSettingsDataTransferImportActionInput
{
    #[ApiProperty(description: 'ID of the import to act on.')]
    #[Groups(['mutation'])]
    public ?int $importId = null;
}
