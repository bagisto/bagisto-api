<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationValues;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationValuesProvider;

class AdminConfigurationValuesQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminConfigurationSchemaResolver $resolver) {}

    public function __invoke(?object $item, array $context): AdminConfigurationValues
    {
        AdminReadPermissions::authorize(null, AdminConfigurationValues::class);

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
