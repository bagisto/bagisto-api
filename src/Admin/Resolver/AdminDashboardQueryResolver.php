<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminDashboard;
use Webkul\BagistoApi\Admin\State\AdminDashboardProvider;
use Webkul\BagistoApi\Exception\AuthorizationException;

/**
 * GraphQL resolver for `adminDashboardStats`.
 *
 * The resolver itself runs through the GraphQL query input — it pushes
 * received args into the live request so the Dashboard helper picks them
 * up via `request()->query()`, then delegates to AdminDashboardProvider.
 */
class AdminDashboardQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): AdminDashboard
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthorizationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $args = $context['args'] ?? [];

        // Forward GraphQL args to the live request so the helper sees them.
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
