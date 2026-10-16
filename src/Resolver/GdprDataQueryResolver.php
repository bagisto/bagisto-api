<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\GdprData;
use Webkul\BagistoApi\State\GdprDataProvider;

class GdprDataQueryResolver implements QueryItemResolverInterface
{
    public function __construct(private readonly GdprDataProvider $provider) {}

    public function __invoke(?object $item, array $context): GdprData
    {
        return $this->provider->build();
    }
}
