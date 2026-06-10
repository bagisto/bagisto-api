<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for the create / update import operations.
 *
 * REST sends these as multipart/form-data alongside the binary `file` part —
 * the processor reads the scalar fields directly from the request, so this DTO
 * exists primarily as the OpenAPI / GraphQL schema reference. GraphQL `create`
 * is rejected (binary upload is REST-only).
 */
class AdminSettingsDataTransferImportCreateInput
{
    #[ApiProperty(description: 'Importer entity type (e.g. products, customers, tax_rates).')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    #[ApiProperty(description: 'Import action: append or delete.')]
    #[Groups(['mutation'])]
    public ?string $action = null;

    #[ApiProperty(description: 'Validation strategy: stop-on-errors or skip-errors.')]
    #[Groups(['mutation'])]
    public ?string $validationStrategy = null;

    #[ApiProperty(description: 'Allowed error count before the import is rejected.')]
    #[Groups(['mutation'])]
    public ?int $allowedErrors = null;

    #[ApiProperty(description: 'CSV field separator character.')]
    #[Groups(['mutation'])]
    public ?string $fieldSeparator = null;

    #[ApiProperty(description: 'Whether to process the import in the queue.')]
    #[Groups(['mutation'])]
    public ?bool $processInQueue = null;

    #[ApiProperty(description: 'Storage path holding images referenced by the import.')]
    #[Groups(['mutation'])]
    public ?string $imagesDirectoryPath = null;
}
