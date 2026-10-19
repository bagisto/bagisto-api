<?php

namespace Webkul\BagistoApi\Facades;

use Illuminate\Support\Facades\Facade;

class CartTokenFacade extends Facade
{
    /**
     * Get facade accessor.
     */
    protected static function getFacadeAccessor()
    {
        return 'cart-token-service';
    }
}
