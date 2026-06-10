<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImport;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Helpers\Import as ImportHelper;
use Webkul\DataTransfer\Models\Import;

/**
 * GET /api/admin/settings/data-transfer/imports/{id}/stats?state=
 *
 * Returns the import detail plus the per-batch progress + summary block for the
 * requested state (default processed). Mirrors ImportController::stats.
 */
class AdminSettingsDataTransferImportStatsProvider implements ProviderInterface
{
    public function __construct(
        protected ImportHelper $importHelper,
        protected AdminSettingsDataTransferImportItemProvider $itemProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminSettingsDataTransferImport
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

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
