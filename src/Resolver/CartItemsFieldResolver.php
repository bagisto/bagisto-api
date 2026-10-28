<?php

namespace Webkul\BagistoApi\Resolver;

use Webkul\BagistoApi\Dto\CartData;

class CartItemsFieldResolver
{
    /**
     * Handle the invoke magic method.
     */
    public function __invoke($rootValue, array $args, $context, $info)
    {
        if (
            $rootValue instanceof CartData
            && isset($rootValue->items)
        ) {
            return $rootValue->items;
        }

        if (
            is_array($rootValue)
            && isset($rootValue['items'])
        ) {
            return $rootValue['items'];
        }

        return null;
    }
}
