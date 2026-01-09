<?php

namespace Webkul\BagistoApi\Exception;

/**
 * OperationFailedException
 *
 * Thrown when an operation fails due to system errors or unexpected conditions.
 * This is for failures during actual processing, not input validation errors.
 *
 * Examples:
 * - Failed to add product to cart (system error)
 * - Failed to update cart item (system error)
 * - Failed to remove item from cart
 * - Failed to apply coupon
 * - Failed to estimate shipping
 * - Failed to merge carts
 *
 * Status Code: 500 Internal Server Error (but ClientAware for GraphQL)
 */
class OperationFailedException extends \Exception implements \GraphQL\Error\ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'operation_failed';
    }
}
