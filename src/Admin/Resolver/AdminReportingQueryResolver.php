<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\State\AdminReportingProvider;
use Webkul\BagistoApi\Exception\AuthorizationException;

/**
 * Base GraphQL resolver for reporting queries. One concrete subclass per
 * sub-page wires the `$entity` and the output resource class.
 */
abstract class AdminReportingQueryResolver implements QueryItemResolverInterface
{
    /** Sub-page: overview | sales | customers | products. */
    protected string $entity;

    /** FQCN of the output resource class. */
    protected string $resourceClass;

    public function __invoke(?object $item, array $context): object
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthorizationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $args = $context['args'] ?? [];

        foreach (['type', 'start', 'end', 'channel'] as $key) {
            if (array_key_exists($key, $args)) {
                request()->query->set($key, $args[$key]);
            }
        }

        $data = AdminReportingProvider::buildPayload($this->entity, $args['type'] ?? null);

        $resource = new $this->resourceClass;
        $resource->entity = $data['entity'];
        $resource->type = $data['type'];
        $resource->dateRange = $data['dateRange'];
        $resource->statistics = $data['statistics'];

        return $resource;
    }
}
