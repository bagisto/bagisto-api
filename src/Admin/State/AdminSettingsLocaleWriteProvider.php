<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsLocale;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Minimal placeholder provider for PUT / DELETE on AdminSettingsLocale.
 *
 * API Platform requires a provider on PUT/DELETE so it can resolve the resource
 * before passing it to the processor. The real lookup lives inside the
 * processor.
 */
class AdminSettingsLocaleWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminSettingsLocale
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $placeholder = new AdminSettingsLocale;
        $placeholder->id = (int) ($uriVariables['id'] ?? 0);

        return $placeholder;
    }
}
