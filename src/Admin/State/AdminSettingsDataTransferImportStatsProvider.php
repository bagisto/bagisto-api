<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImport;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportStats;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Helpers\Import as ImportHelper;
use Webkul\DataTransfer\Models\Import;

class AdminSettingsDataTransferImportStatsProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected ImportHelper $importHelper,
        protected AdminSettingsDataTransferImportItemProvider $itemProvider,
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminSettingsDataTransferImport
    {
        AdminReadPermissions::authorize($operation, AdminSettingsDataTransferImportStats::class);

        $id = (int) ($uriVariables['id'] ?? request()->route('id') ?? 0);

        $import = Import::find($id);
        if (! $import) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
        }

        $state = (string) (request()->query('state') ?? ImportHelper::STATE_PROCESSED);

        $stats = $this->importHelper->setImport($import)->stats($state);

        $result = $this->itemProvider->mapToDtoPublic($this->importHelper->getImport());
        $result->stats = $stats;

        return $result;
    }
}
