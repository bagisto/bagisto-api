<?php

namespace Webkul\BagistoApi\Facades;

use Illuminate\Support\Facades\Facade;

class TokenHeaderFacade extends Facade
{
    /**
     * Get facade accessor.
     */
    protected static function getFacadeAccessor()
    {
        return 'token-header-service';
    }
}
