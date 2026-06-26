<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsChannel;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Minimal placeholder provider for AdminSettingsChannel PUT/DELETE.
 *
 * API Platform requires a provider on PUT/DELETE so it can resolve the resource
 * before passing it to the processor. The actual lookup happens in the processor.
 */
class AdminSettingsChannelWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminSettingsChannel
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return (new AdminSettingsChannel)->forceFill([
            'id' => (int) ($uriVariables['id'] ?? 0),
        ]);
    }
}
