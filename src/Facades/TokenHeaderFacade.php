<?php

namespace Webkul\BagistoApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * TokenHeaderFacade - Facade for extracting Bearer tokens from Authorization headers
 *
 * @method static string|null getAuthorizationBearerToken(\Illuminate\Http\Request $request)
 * @method static bool hasAuthorizationToken(\Illuminate\Http\Request $request)
 * @method static string|null extractToken(\Illuminate\Http\Request $request)
 *
 * @see \Webkul\BagistoApi\Services\TokenHeaderService
 */
class TokenHeaderFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'token-header-service';
    }
}
