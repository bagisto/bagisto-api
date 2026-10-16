<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\State\Concerns\FindsCartCrossSells;

/**
 * REST: cross-sell products for the cart of the Bearer token.
 */
class CartCrossSellProvider implements ProviderInterface
{
    use FindsCartCrossSells;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->cartCrossSellProducts()->all();
    }
}
