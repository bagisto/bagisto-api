<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminBookingProduct;
use Webkul\BagistoApi\Admin\State\AdminBookingProductProvider;

class AdminBookingProductConfigResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminBookingProductProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminBookingProduct
    {
        return $this->provider->config((int) ($context['args']['productId'] ?? 0));
    }
}
