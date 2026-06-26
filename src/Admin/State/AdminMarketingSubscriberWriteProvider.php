<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * Placeholder provider for PUT / DELETE on AdminMarketingSubscriber.
 */
class AdminMarketingSubscriberWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AdminMarketingSubscriber
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        return (new AdminMarketingSubscriber)->forceFill([
            'id' => (int) ($uriVariables['id'] ?? 0),
        ]);
    }
}
