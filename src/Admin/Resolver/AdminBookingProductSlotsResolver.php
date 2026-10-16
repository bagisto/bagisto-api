<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Admin\Models\AdminBookingProductSlots;
use Webkul\BagistoApi\Admin\State\AdminBookingProductProvider;

class AdminBookingProductSlotsResolver implements QueryItemResolverInterface
{
    public function __construct(protected AdminBookingProductProvider $provider) {}

    public function __invoke(?object $item, array $context): AdminBookingProductSlots
    {
        return $this->provider->slots((int) ($context['args']['productId'] ?? 0), $context['args']['date'] ?? null);
    }
}
