<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportActionInput;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportValidation;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Helpers\Import as ImportHelper;
use Webkul\DataTransfer\Models\Import;

/**
 * Large-file validation, mirroring ImportController::validateChunk, ::validateQueued
 * and ::validateStatus: a browser-driven window, a queued batch, and its progress.
 */
class AdminSettingsDataTransferImportValidationProcessor implements ProcessorInterface, ProviderInterface
{
    public function __construct(protected ImportHelper $importHelper) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminSettingsDataTransferImportValidation
    {
        $import = $this->findImport($this->resolveImportId($data, $uriVariables, $context));

        $queue = $operation instanceof Mutation
            ? $operation->getName() === 'queue'
            : str_ends_with((string) $operation->getUriTemplate(), '/queue');

        return $queue ? $this->queue($import) : $this->chunk($import);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminSettingsDataTransferImportValidation
    {
        return $this->status((int) ($uriVariables['id'] ?? request()->route('id') ?? 0));
    }

    public function status(int $importId): AdminSettingsDataTransferImportValidation
    {
        $import = $this->findImport($importId);

        return $this->attempt($import, function () use ($import) {
            $this->importHelper->setImport($import);

            $progress = $this->importHelper->queuedValidationProgress();

            if ($progress['done']) {
                $progress['is_valid'] = $this->importHelper->isValid();

                $progress['import'] = $this->importHelper->getImport()->unsetRelations();
            }

            return $progress;
        });
    }

    protected function chunk(Import $import): AdminSettingsDataTransferImportValidation
    {
        return $this->attempt($import, function () use ($import) {
            $progress = $this->importHelper->setImport($import)->validateChunk();

            $total = (int) ($progress['total'] ?? 0);

            $progress['progress'] = $total > 0
                ? (int) floor(min((int) $progress['processed'], $total) / $total * 100)
                : 100;

            return $progress;
        });
    }

    protected function queue(Import $import): AdminSettingsDataTransferImportValidation
    {
        return $this->attempt($import, function () use ($import) {
            $total = $this->importHelper->setImport($import)->queueValidation();

            return ['total' => $total, 'processed' => 0, 'progress' => $total ? 0 : 100, 'done' => ! $total];
        });
    }

    /**
     * Run one step and map its progress; any failure is a 400, as in core's controller.
     */
    protected function attempt(Import $import, callable $step): AdminSettingsDataTransferImportValidation
    {
        try {
            $progress = $step();
        } catch (\Throwable $e) {
            report($e);

            throw new InvalidInputException(__('bagistoapi::app.admin.settings.data-transfer.import.validation-failed'), 400);
        }

        $record = $progress['import'] ?? null;

        $result = new AdminSettingsDataTransferImportValidation;
        $result->id = (int) $import->id;
        $result->state = (string) $import->fresh()->state;
        $result->total = (int) ($progress['total'] ?? 0);
        $result->processed = (int) ($progress['processed'] ?? 0);
        $result->invalid = isset($progress['invalid']) ? (int) $progress['invalid'] : null;
        $result->progress = (int) ($progress['progress'] ?? 0);
        $result->done = (bool) ($progress['done'] ?? false);
        $result->is_valid = isset($progress['is_valid']) ? (bool) $progress['is_valid'] : null;
        $result->import = $record instanceof Import ? $record->toArray() : null;

        return $result;
    }

    protected function findImport(int $importId): Import
    {
        AdminReadPermissions::authorize(null, null, 'settings.data_transfer.imports.import');

        $import = $importId > 0 ? Import::find($importId) : null;

        if (! $import) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
        }

        return $import;
    }

    protected function resolveImportId(mixed $data, array $uriVariables, array $context): int
    {
        if ($data instanceof AdminSettingsDataTransferImportActionInput && $data->importId) {
            return (int) $data->importId;
        }

        return (int) ($uriVariables['id']
            ?? $context['args']['input']['importId']
            ?? request()->route('id')
            ?? 0);
    }
}
