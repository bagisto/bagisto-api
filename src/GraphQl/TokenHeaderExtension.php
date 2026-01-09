<?php

namespace Webkul\BagistoApi\GraphQl;

use Illuminate\Http\Request;

/**
 * @deprecated Token extraction now happens in processors/providers only
 */
class TokenHeaderExtension
{
    public static function injectHeaderToken($data, ?Request $request = null): mixed
    {
        return $data;
    }
}
