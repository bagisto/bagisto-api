<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Dto\CheckoutAddressOutput;
use Webkul\BagistoApi\State\CheckoutAddressProvider;

class CheckoutAddressQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(protected CheckoutAddressProvider $provider) {}

    /**
     * Resolve the addresses of the cart identified by the bearer token.
     */
    public function __invoke(?object $item, array $context): CheckoutAddressOutput
    {
        return $this->provider->provideForCart();
    }
}
