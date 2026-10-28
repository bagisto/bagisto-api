<?php

namespace Webkul\BagistoApi\GraphQl;

use Illuminate\Http\Request;

class TokenHeaderExtension
{
    /**
     * Inject header token.
     */
    public static function injectHeaderToken($data, ?Request $request = null): mixed
    {
        return $data;
    }
}
