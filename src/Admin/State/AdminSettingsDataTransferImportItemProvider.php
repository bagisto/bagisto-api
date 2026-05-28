<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImport;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\DataTransfer\Models\Import;

class AdminSettingsDataTransferImportItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.data-transfer.import.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Import::find($id);
    }

    protected function mapToDto(object $import): AdminSettingsDataTransferImport
    {
        /** @var Import $import */
        $dto = new AdminSettingsDataTransferImport;

        $dto->id = (int) $import->id;
        $dto->code = $import->type;
        $dto->action = $import->action;
        $dto->state = $import->state;
        $dto->processInQueue = isset($import->process_in_queue) ? (bool) $import->process_in_queue : null;
        $dto->validationStrategy = $import->validation_strategy;
        $dto->allowedErrors = isset($import->allowed_errors) ? (int) $import->allowed_errors : null;
        $dto->processedRowsCount = isset($import->processed_rows_count) ? (int) $import->processed_rows_count : null;
        $dto->invalidRowsCount = isset($import->invalid_rows_count) ? (int) $import->invalid_rows_count : null;
        $dto->errorsCount = isset($import->errors_count) ? (int) $import->errors_count : null;
        $dto->errors = is_array($import->errors) ? $import->errors : null;
        $dto->fieldSeparator = $import->field_separator;
        $dto->filePath = $import->file_path;
        $dto->imagesDirectoryPath = $import->images_directory_path;
        $dto->errorFilePath = $import->error_file_path;
        $dto->summary = is_array($import->summary) ? $import->summary : null;
        $dto->startedAt = $import->started_at?->toIso8601String();
        $dto->completedAt = $import->completed_at?->toIso8601String();
        $dto->createdAt = $import->created_at?->toIso8601String();
        $dto->updatedAt = $import->updated_at?->toIso8601String();

        return $dto;
    }

    /**
     * Public alias used by processors to share the mapping logic.
     */
    public function mapToDtoPublic(object $import): AdminSettingsDataTransferImport
    {
        return $this->mapToDto($import);
    }
}
