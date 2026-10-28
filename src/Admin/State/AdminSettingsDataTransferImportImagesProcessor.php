<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportActionInput;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportImages;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Helpers\Import as ImportHelper;
use Webkul\DataTransfer\Models\Import;

/**
 * Import image phase, mirroring ImportController::downloadImages, ::downloadImagesQueued
 * and ::downloadImagesStatus: a browser-driven wave, a queued batch, and its progress.
 */
class AdminSettingsDataTransferImportImagesProcessor implements ProcessorInterface, ProviderInterface
{
    public function __construct(protected ImportHelper $importHelper) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminSettingsDataTransferImportImages
    {
        $import = $this->findImport($this->resolveImportId($data, $uriVariables, $context));

        $queue = $operation instanceof Mutation
            ? $operation->getName() === 'queue'
            : str_ends_with((string) $operation->getUriTemplate(), '/queue');

        return $queue ? $this->queue($import) : $this->download($import);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminSettingsDataTransferImportImages
    {
        return $this->status((int) ($uriVariables['id'] ?? request()->route('id') ?? 0));
    }

    public function status(int $importId): AdminSettingsDataTransferImportImages
    {
        $import = $this->findImport($importId);

        return $this->attempt($import, fn () => $this->importHelper->setImport($import)->queuedImageProgress());
    }

    protected function download(Import $import): AdminSettingsDataTransferImportImages
    {
        return $this->attempt($import, fn () => $this->importHelper->setImport($import)->downloadImages());
    }

    protected function queue(Import $import): AdminSettingsDataTransferImportImages
    {
        return $this->attempt($import, function () use ($import) {
            $total = $this->importHelper->setImport($import)->queueImageDownload();

            return ['total' => $total, 'processed' => 0, 'progress' => $total ? 0 : 100, 'done' => ! $total];
        });
    }

    /**
     * Run one step and map its progress; any failure is a 400, as in core's controller.
     */
    protected function attempt(Import $import, callable $step): AdminSettingsDataTransferImportImages
    {
        try {
            $progress = $step();
        } catch (\Throwable $e) {
            report($e);

            throw new InvalidInputException(__('bagistoapi::app.admin.settings.data-transfer.import.image-download-failed'), 400);
        }

        $result = new AdminSettingsDataTransferImportImages;
        $result->id = (int) $import->id;
        $result->state = (string) $import->fresh()->state;
        $result->total = (int) ($progress['total'] ?? 0);
        $result->processed = (int) ($progress['processed'] ?? 0);
        $result->progress = (int) ($progress['progress'] ?? 0);
        $result->done = (bool) ($progress['done'] ?? false);

        return $result;
    }

    protected function findImport(int $importId): Import
    {
        AdminReadPermissions::authorize(null, AdminSettingsDataTransferImportImages::class);

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
