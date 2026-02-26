<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reads X-Locale and X-Channel headers from API requests and binds
 * them into the request attributes so providers/resolvers can use them.
 *
 * If the headers are not sent, the current application locale and
 * default channel are used — existing behaviour is preserved.
 *
 * Headers:
 *   X-Locale  — locale code, e.g. "en", "fr", "ar"
 *   X-Channel — channel code, e.g. "default"
 */
class SetLocaleChannel
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale  = $request->header('X-Locale');
        $channel = $request->header('X-Channel');

        if ($locale) {
            app()->setLocale($locale);
            $request->attributes->set('bagisto_locale', $locale);
        }

        if ($channel) {
            $request->attributes->set('bagisto_channel', $channel);
        }

        return $next($request);
    }
}
