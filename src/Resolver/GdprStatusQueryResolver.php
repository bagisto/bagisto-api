<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\GdprStatus;
use Webkul\BagistoApi\State\FeatureStatusProvider;

class GdprStatusQueryResolver implements QueryItemResolverInterface
{
    /**
     * Handle the invoke magic method.
     */
    public function __invoke(?object $item, array $context): GdprStatus
    {
        return FeatureStatusProvider::build(GdprStatus::class);
    }
}
