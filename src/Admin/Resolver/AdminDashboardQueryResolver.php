<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminDashboard;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Admin\State\AdminDashboardProvider;
use Webkul\BagistoApi\Exception\AuthorizationException;

class AdminDashboardQueryResolver implements QueryItemResolverInterface
{
    /**
     * Handle the invoke magic method.
     */
    public function __invoke(?object $item, array $context): AdminDashboard
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthorizationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        AdminReadPermissions::authorize(null, AdminDashboard::class);

        $args = $context['args'] ?? [];

        foreach (['type', 'start', 'end', 'channel'] as $key) {
            if (array_key_exists($key, $args)) {
                request()->query->set($key, $args[$key]);
            }
        }

        $data = AdminDashboardProvider::buildPayload($args['type'] ?? null);

        $dashboard = new AdminDashboard;
        $dashboard->type = $data['type'];
        $dashboard->dateRange = $data['dateRange'];
        $dashboard->statistics = $data['statistics'];

        return $dashboard;
    }
}
