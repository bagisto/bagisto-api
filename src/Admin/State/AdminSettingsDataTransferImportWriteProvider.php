<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImport;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Minimal placeholder provider for AdminSettingsDataTransferImport DELETE.
 *
 * API Platform requires a provider on Delete operations so it can resolve the
 * resource before dispatching to the processor. Actual lookup + state mutation
 * happens inside AdminSettingsDataTransferImportProcessor.
 */
class AdminSettingsDataTransferImportWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminSettingsDataTransferImport
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminSettingsDataTransferImport;
        $placeholder->id = (int) ($uriVariables['id'] ?? 0);

        return $placeholder;
    }
}
