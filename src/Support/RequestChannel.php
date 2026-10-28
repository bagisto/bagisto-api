<?php

namespace Webkul\BagistoApi\Support;

use Illuminate\Http\Request;
use Webkul\Core\Models\Channel;
use Webkul\Core\Repositories\ChannelRepository;

class RequestChannel
{
    /**
     * The header a storefront request names its channel with.
     */
    public const HEADER = 'X-Channel';

    /**
     * The channel the request names in its X-Channel header, or null when it names none or an unknown one.
     */
    public static function fromHeader(Request $request): ?Channel
    {
        $code = $request->header(self::HEADER);

        if (
            ! is_string($code)
            || $code === ''
        ) {
            return null;
        }

        return app(ChannelRepository::class)->findOneByField('code', $code);
    }

    /**
     * The channel a storefront request runs on: the one it names in X-Channel, else the current one.
     */
    public static function resolve(Request $request): ?Channel
    {
        return self::fromHeader($request) ?? core()->getCurrentChannel();
    }
}
