<?php

namespace Webkul\BagistoApi\CacheProfiles;

use Illuminate\Http\Request;
use Webkul\FPC\CacheProfiles\FullPageCacheProfile;

class ApiAwareResponseCache extends FullPageCacheProfile
{
    /**
     * Request paths that are never read from or written to the page cache.
     */
    public const API_PATHS = ['api', 'api/*', 'graphql', 'graphql/*', 'graphiql', 'graphiql/*'];

    /**
     * Whether the page cache should run for this request.
     */
    public function enabled(Request $request): bool
    {
        if ($this->isApiRequest($request)) {
            return false;
        }

        return parent::enabled($request);
    }

    /**
     * Determine if the request should be cached.
     */
    public function shouldCacheRequest(Request $request): bool
    {
        if ($this->isApiRequest($request)) {
            return false;
        }

        return parent::shouldCacheRequest($request);
    }

    /**
     * Whether the request targets the REST or GraphQL API.
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->is(...self::API_PATHS);
    }
}
