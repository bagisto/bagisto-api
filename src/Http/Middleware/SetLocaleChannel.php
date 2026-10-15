<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Core\Repositories\ChannelRepository;

class SetLocaleChannel
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(protected ChannelRepository $channelRepository) {}

    /**
     * Apply the X-Channel, X-Locale and X-Currency headers to the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $channelCode = $request->header('X-Channel');

        if (
            is_string($channelCode)
            && $channelCode !== ''
        ) {
            $requestedChannel = $this->channelRepository->findOneByField('code', $channelCode);

            if (! $requestedChannel) {
                return response()->json([
                    'message' => trans('bagistoapi::app.graphql.headers.unknown-channel', ['code' => $channelCode]),
                    'error' => 'invalid_channel',
                    'header_name' => 'X-Channel',
                ], 400);
            }

            core()->setCurrentChannel($requestedChannel);

            $request->attributes->set('bagisto_channel', $requestedChannel->code);
        }

        $channel = core()->getCurrentChannel();

        if (! $channel) {
            return $next($request);
        }

        $locale = $request->header('X-Locale');
        $availableLocales = $channel->locales ? $channel->locales->pluck('code')->toArray() : [];
        $defaultLocale = $channel->default_locale?->code;

        if (
            $locale
            && in_array($locale, $availableLocales)
        ) {
            app()->setLocale($locale);
            $request->attributes->set('bagisto_locale', $locale);
        } elseif ($defaultLocale) {
            app()->setLocale($defaultLocale);
            $request->attributes->set('bagisto_locale', $defaultLocale);
        }

        $currency = $request->header('X-Currency');
        $availableCurrencies = $channel->currencies ? $channel->currencies->pluck('code')->toArray() : [];
        $defaultCurrency = $channel->base_currency?->code;

        if (
            $currency
            && in_array($currency, $availableCurrencies)
        ) {
            core()->setCurrentCurrency($currency);
            $request->attributes->set('bagisto_currency', $currency);
        } elseif ($defaultCurrency) {
            core()->setCurrentCurrency($defaultCurrency);
            $request->attributes->set('bagisto_currency', $defaultCurrency);
        }

        return $next($request);
    }
}
