<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Dto\CheckoutMessageOutput;
use Webkul\BagistoApi\State\CheckoutMessageProvider;

class CheckoutMessageQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(protected CheckoutMessageProvider $provider) {}

    /**
     * Resolve the checkout success message for the order in the query arguments.
     */
    public function __invoke(?object $item, array $context): CheckoutMessageOutput
    {
        return $this->provider->provideForOrder((int) ($context['args']['orderId'] ?? 0));
    }
}
