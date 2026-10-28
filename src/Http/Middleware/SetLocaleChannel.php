<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NumberFormatter;
use Symfony\Component\HttpFoundation\Response;
use ValueError;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Customer\Contracts\Customer;

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

        if (is_string($channelCode) && $channelCode !== '') {
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

        $this->actAsBearerCustomer($request);

        $channel = core()->getCurrentChannel();

        if (! $channel) {
            return $next($request);
        }

        $locale = $request->header('X-Locale');
        $availableLocales = $channel->locales ? $channel->locales->pluck('code')->toArray() : [];
        $defaultLocale = $channel->default_locale?->code;

        if ($locale && in_array($locale, $availableLocales) && $this->isUsableLocale($locale)) {
            app()->setLocale($locale);
            $request->attributes->set('bagisto_locale', $locale);
        } elseif ($defaultLocale) {
            app()->setLocale($defaultLocale);
            $request->attributes->set('bagisto_locale', $defaultLocale);
        }

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

    /**
     * Whether PHP's intl can format numbers and prices in this locale.
     *
     * Core accepts any letters as a locale code, but it formats every price with NumberFormatter,
     * which throws on a code ICU does not know (for example `Jp`, where `ja` is meant). Such a
     * locale is treated like one the channel does not offer: the default locale is used instead.
     */
    protected function isUsableLocale(string $locale): bool
    {
        try {
            new NumberFormatter($locale, NumberFormatter::CURRENCY);
        } catch (ValueError) {
            return false;
        }

        return true;
    }

    /**
     * Core reads the customer group and the cart from the session guard, so a storefront
     * request with a customer bearer token must sign that customer in for the request.
     */
    protected function actAsBearerCustomer(Request $request): void
    {
        if (! $request->bearerToken() || str_starts_with($request->getPathInfo(), '/api/admin')) {
            return;
        }

        $customer = Auth::guard('sanctum')->user();

        if ($customer instanceof Customer) {
            Auth::guard('customer')->setUser($customer);
        }
    }
}
