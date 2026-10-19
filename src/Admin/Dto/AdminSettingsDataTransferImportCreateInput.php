<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsDataTransferImportCreateInput
{
    /**
     * The type.
     */
    #[ApiProperty(description: 'Importer entity type (e.g. products, customers, tax_rates).')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    /**
     * The action.
     */
    #[ApiProperty(description: 'Import action: append or delete.')]
    #[Groups(['mutation'])]
    public ?string $action = null;

    /**
     * The validation strategy.
     */
    #[ApiProperty(description: 'Validation strategy: stop-on-errors or skip-errors.')]
    #[Groups(['mutation'])]
    public ?string $validationStrategy = null;

    /**
     * The allowed errors.
     */
    #[ApiProperty(description: 'Allowed error count before the import is rejected.')]
    #[Groups(['mutation'])]
    public ?int $allowedErrors = null;

    /**
     * The field separator.
     */
    #[ApiProperty(description: 'CSV field separator character.')]
    #[Groups(['mutation'])]
    public ?string $fieldSeparator = null;

    /**
     * The process in queue.
     */
    #[ApiProperty(description: 'Whether to process the import in the queue.')]
    #[Groups(['mutation'])]
    public ?bool $processInQueue = null;

    /**
     * The images directory path.
     */
    #[ApiProperty(description: 'Storage path holding images referenced by the import.')]
    #[Groups(['mutation'])]
    public ?string $imagesDirectoryPath = null;
}
