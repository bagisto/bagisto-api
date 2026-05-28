<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationValues;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationValuesProvider;
use Webkul\BagistoApi\Exception\AuthorizationException;

class AdminConfigurationValuesQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminConfigurationSchemaResolver $resolver) {}

    public function __invoke(?object $item, array $context): AdminConfigurationValues
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthorizationException(__('bagistoapi::app.admin.configuration.unauthenticated'));
        }

        $args = $context['args'] ?? [];
        $payload = AdminConfigurationValuesProvider::buildPayload(
            $this->resolver,
            $args['slug'] ?? null,
            $args['channel'] ?? null,
            $args['locale'] ?? null,
        );

        return AdminConfigurationValuesProvider::toDto($payload);
    }
}
