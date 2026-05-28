<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationMenu;
use Webkul\BagistoApi\Admin\State\AdminConfigurationMenuProvider;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver;
use Webkul\BagistoApi\Exception\AuthorizationException;

class AdminConfigurationMenuQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminConfigurationSchemaResolver $resolver) {}

    public function __invoke(?object $item, array $context): AdminConfigurationMenu
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthorizationException(__('bagistoapi::app.admin.configuration.unauthenticated'));
        }

        $args = $context['args'] ?? [];
        $payload = AdminConfigurationMenuProvider::buildPayload(
            $this->resolver,
            $args['slug'] ?? null,
            (bool) ($args['include_values'] ?? false),
            $args['channel'] ?? null,
            $args['locale'] ?? null,
        );

        return AdminConfigurationMenuProvider::toDto($payload);
    }
}
