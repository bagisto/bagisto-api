<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationSlug;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSlugProvider;

class AdminConfigurationSlugQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminConfigurationSchemaResolver $resolver) {}

    public function __invoke(?object $item, array $context): AdminConfigurationSlug
    {
        AdminReadPermissions::authorize(null, AdminConfigurationSlug::class);

        return AdminConfigurationSlugProvider::toDto(
            AdminConfigurationSlugProvider::buildPayload($this->resolver),
        );
    }
}
