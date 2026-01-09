<?php

namespace Webkul\BagistoApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object|null getCartByToken(string $token)
 * @method static object|null getCartById(int $cartId)
 * @method static object|null getCustomerByToken(string $token)
 * @method static object|null getGuestTokenRecord(string $token)
 * @method static string getTokenType(string $token)
 * @method static bool isValidToken(string $token)
 */
class CartTokenFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cart-token-service';
    }
}
