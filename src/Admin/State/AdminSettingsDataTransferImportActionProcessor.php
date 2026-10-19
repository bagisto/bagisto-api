<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportActionInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImport;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Helpers\Import as ImportHelper;
use Webkul\DataTransfer\Models\Import;

class AdminSettingsDataTransferImportActionProcessor implements ProcessorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected ImportHelper $importHelper,
        protected AdminSettingsDataTransferImportItemProvider $itemProvider,
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'settings.data_transfer.imports.import');

        $id = $this->resolveImportId($data, $uriVariables, $context);
        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
        }

        $import = Import::find($id);
        if (! $import) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
        }

        $action = $this->resolveAction($operation);

        return match ($action) {
            'validate' => $this->validate($import),
            'start' => $this->start($import),
            'link' => $this->link($import),
            'index' => $this->index($import),
            default => throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.data-transfer.import.not-found'),
                404,
            ),
        };
    }

    /**
     * Validate.
     */
    protected function validate(Import $import): AdminSettingsDataTransferImport
    {
        $isValid = $this->importHelper->setImport($import)->validate();

        $result = $this->itemProvider->mapToDtoPublic($this->importHelper->getImport());
        $result->is_valid = $isValid;
        $result->success = true;

        return $result;
    }

    /**
     * Start.
     */
    protected function start(Import $import): AdminSettingsDataTransferImport
    {
        $this->assertProcessableRows($import);

        $this->importHelper->setImport($import);

        if (! $this->importHelper->isValid()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.data-transfer.import.not-valid'), 400);
        }

        if (
            $import->process_in_queue
            && config('queue.default') == 'sync'
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.data-transfer.import.setup-queue-error'), 400);
        }

        if ($import->state == ImportHelper::STATE_VALIDATED) {
            $this->importHelper->started();
        }

        $importBatch = $import->batches->where('state', ImportHelper::STATE_PENDING)->first();

        if ($importBatch) {
            try {
                if ($import->process_in_queue) {
                    $this->importHelper->start();
                } else {
                    $this->importHelper->start($importBatch);
                }
            } catch (\Exception $exception) {
                throw new InvalidInputException($exception->getMessage(), 400);
            }
        } else {
            if ($this->importHelper->isLinkingRequired()) {
                $this->importHelper->linking();
            } elseif ($this->importHelper->isIndexingRequired()) {
                $this->importHelper->indexing();
            } else {
                $this->importHelper->completed();
            }
        }

        return $this->statsResult(ImportHelper::STATE_PROCESSED);
    }

    /**
     * Link.
     */
    protected function link(Import $import): AdminSettingsDataTransferImport
    {
        $this->assertProcessableRows($import);

        $this->importHelper->setImport($import);

        if (! $this->importHelper->isValid()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.data-transfer.import.not-valid'), 400);
        }

        if ($import->state == ImportHelper::STATE_PROCESSED) {
            $this->importHelper->linking();
        }

        $importBatch = $import->batches->where('state', ImportHelper::STATE_PROCESSED)->first();

        if ($importBatch) {
            try {
                $this->importHelper->link($importBatch);
            } catch (\Exception $exception) {
                throw new InvalidInputException($exception->getMessage(), 400);
            }
        } else {
            if ($this->importHelper->isIndexingRequired()) {
                $this->importHelper->indexing();
            } else {
                $this->importHelper->completed();
            }
        }

        return $this->statsResult(ImportHelper::STATE_LINKED);
    }

    /**
     * Index.
     */
    protected function index(Import $import): AdminSettingsDataTransferImport
    {
        $this->assertProcessableRows($import);

        $this->importHelper->setImport($import);

        if (! $this->importHelper->isValid()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.data-transfer.import.not-valid'), 400);
        }

        if ($import->state == ImportHelper::STATE_LINKED) {
            $this->importHelper->indexing();
        }

        $importBatch = $import->batches->where('state', ImportHelper::STATE_LINKED)->first();

        if ($importBatch) {
            try {
                $this->importHelper->index($importBatch);
            } catch (\Exception $exception) {
                throw new InvalidInputException($exception->getMessage(), 400);
            }
        } else {
            $this->importHelper->completed();
        }

        return $this->statsResult(ImportHelper::STATE_INDEXED);
    }

    /**
     * Stats result.
     */
    protected function statsResult(string $state): AdminSettingsDataTransferImport
    {
        $stats = $this->importHelper->stats($state);

        $result = $this->itemProvider->mapToDtoPublic($this->importHelper->getImport());
        $result->stats = $stats;
        $result->success = true;

        return $result;
    }

    /**
     * Assert processable rows.
     */
    protected function assertProcessableRows(Import $import): void
    {
        if (! $import->processed_rows_count) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.data-transfer.import.nothing-to-import'), 400);
        }
    }

    /**
     * Resolve action.
     */
    protected function resolveAction(Operation $operation): string
    {
        $shortName = (string) $operation->getShortName();

        return match (true) {
            str_contains($shortName, 'Validate') => 'validate',
            str_contains($shortName, 'Start') => 'start',
            str_contains($shortName, 'Link') => 'link',
            str_contains($shortName, 'Index') => 'index',
            default => '',
        };
    }

    /**
     * Resolve import id.
     */
    protected function resolveImportId(mixed $data, array $uriVariables, array $context): int
    {
        if (isset($uriVariables['id'])) {
            return (int) $uriVariables['id'];
        }

        if (
            $data instanceof AdminSettingsDataTransferImportActionInput
            && ! empty($data->importId)
        ) {
            return (int) $data->importId;
        }

        $fromArgs = $context['args']['input']['importId']
            ?? $context['args']['importId']
            ?? null;
        if ($fromArgs !== null) {
            return (int) $fromArgs;
        }

        $fromRoute = request()->route('id');
        if ($fromRoute !== null) {
            return (int) $fromRoute;
        }

        return 0;
    }

    /**
     * Assert permission.
     */
    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.data-transfer.import.no-permission'));
        }

        if (($role->permission_type ?? null) === 'all') {
            return;
        }

        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }

        if (
            ! in_array($permission, $perms, true)
            && ! in_array('*', $perms, true)
        ) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.data-transfer.import.no-permission'));
        }
    }
}
