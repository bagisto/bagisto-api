<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reads optional X-Locale, X-Channel and X-Currency headers from API
 * requests and configures the application accordingly.
 *
 * All three headers are optional. When omitted the channel's default
 * value is used. When provided but invalid for the current channel,
 * the channel default is used as a fallback.
 *
 * Headers:
 *   X-LOCALE   — locale code, e.g. "en", "fr", "ar"
 *   X-CHANNEL  — channel code, e.g. "default"
 *   X-CURRENCY — currency code, e.g. "USD", "EUR", "INR"
 */
class SetLocaleChannel
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $channel = core()->getCurrentChannel();

        // If the channel cannot be resolved (e.g. mis-seeded test environment,
        // mid-install, or a transient cache problem) we must NOT 500 the
        // request — this middleware applies globally including to admin
        // endpoints that do not depend on storefront context. Pass through
        // silently and let downstream code handle missing-channel cases.
        if (! $channel) {
            return $next($request);
        }

        // --- Channel ---
        $channelCode = $request->header('X-Channel');

        if ($channelCode) {
            $request->attributes->set('bagisto_channel', $channelCode);
        }

        // --- Locale (optional — defaults to channel's default locale) ---
        $locale = $request->header('X-Locale');
        $availableLocales = $channel->locales ? $channel->locales->pluck('code')->toArray() : [];
        $defaultLocale = $channel->default_locale?->code;

        if ($locale && in_array($locale, $availableLocales)) {
            app()->setLocale($locale);
            $request->attributes->set('bagisto_locale', $locale);
        } elseif ($defaultLocale) {
            app()->setLocale($defaultLocale);
            $request->attributes->set('bagisto_locale', $defaultLocale);
        }

        // --- Currency (optional — defaults to channel's base currency) ---
        $currency = $request->header('X-Currency');
        $availableCurrencies = $channel->currencies ? $channel->currencies->pluck('code')->toArray() : [];
        $defaultCurrency = $channel->base_currency?->code;

        if ($currency && in_array($currency, $availableCurrencies)) {
            core()->setCurrentCurrency($currency);
            $request->attributes->set('bagisto_currency', $currency);
        } elseif ($defaultCurrency) {
            core()->setCurrentCurrency($defaultCurrency);
            $request->attributes->set('bagisto_currency', $defaultCurrency);
        }

        return $next($request);
    }
}
