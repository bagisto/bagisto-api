<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminConfigurationSlug;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSchemaResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSlugProvider;
use Webkul\BagistoApi\Exception\AuthorizationException;

class AdminConfigurationSlugQueryResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminConfigurationSchemaResolver $resolver) {}

    public function __invoke(?object $item, array $context): AdminConfigurationSlug
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthorizationException(__('bagistoapi::app.admin.configuration.unauthenticated'));
        }

        return AdminConfigurationSlugProvider::toDto(
            AdminConfigurationSlugProvider::buildPayload($this->resolver),
        );
    }
}
