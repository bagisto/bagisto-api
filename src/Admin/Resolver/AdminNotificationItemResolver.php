<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminNotification;
use Webkul\BagistoApi\Admin\State\AdminNotificationProvider;

class AdminNotificationItemResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminNotificationProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminNotification
    {
        return $this->provider->find((int) basename((string) ($context['args']['id'] ?? '')));
    }
}
