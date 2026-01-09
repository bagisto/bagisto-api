<?php

namespace Webkul\BagistoApi\Resolver;

use Webkul\BagistoApi\Dto\CartData;

/**
 * Custom resolver for CartData.items field
 *
 * Provides items directly without going through denormalization provider
 * which causes "Undefined array key input" error.
 */
class CartItemsFieldResolver
{
    public function __invoke($rootValue, array $args, $context, $info)
    {
        // If rootValue is CartData with items, return them directly
        if ($rootValue instanceof CartData && isset($rootValue->items)) {
            return $rootValue->items;
        }

        // If rootValue is an array (serialized CartData), return items key
        if (is_array($rootValue) && isset($rootValue['items'])) {
            return $rootValue['items'];
        }

        return null;
    }
}
