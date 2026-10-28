<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use Webkul\BagistoApi\State\Concerns\FindsCartCrossSells;

/**
 * GraphQL: cross-sell products for the cart of the Bearer token.
 */
class CartCrossSellResolver implements QueryCollectionResolverInterface
{
    use FindsCartCrossSells;

    public function __invoke(?iterable $collection, array $context): iterable
    {
        return $this->cartCrossSellProducts()->all();
    }
}
