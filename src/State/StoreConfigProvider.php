<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\StoreConfig;
use Webkul\BagistoApi\Services\SocialLoginTokenVerifier;
use Webkul\Product\Helpers\Toolbar;

/**
 * Reads the public storefront settings of the current channel and locale from core configuration.
 */
class StoreConfigProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [self::build()];
    }

    public static function build(): StoreConfig
    {
        $channel = core()->getRequestedChannelCode();
        $locale = core()->getRequestedLocaleCode();

        $config = new StoreConfig;

        $config->id = $channel;
        $config->channel = $channel;
        $config->locale = $locale;

        $config->guest_checkout_enabled = self::flag('sales.checkout.shopping_cart.allow_guest_checkout');
        $config->cart_cross_sell_enabled = self::flag('sales.checkout.shopping_cart.cross_sell');
        $config->estimate_shipping_enabled = self::flag('sales.checkout.shopping_cart.estimate_shipping');
        $config->reorder_enabled = self::flag('sales.order_settings.reorder.shop');

        $config->minimum_order_enabled = self::flag('sales.order_settings.minimum_order.enable');
        $config->minimum_order_amount = (float) (core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0);
        $config->minimum_order_includes_discount = self::flag('sales.order_settings.minimum_order.include_discount_amount');
        $config->minimum_order_includes_tax = self::flag('sales.order_settings.minimum_order.include_tax_to_amount');
        $config->minimum_order_message = core()->getConfigData('sales.order_settings.minimum_order.description') ?: null;

        $config->customer_review_enabled = self::flag('catalog.products.review.customer_review');
        $config->guest_review_enabled = self::flag('catalog.products.review.guest_review');
        $config->reviewer_name_censored = self::flag('catalog.products.review.censoring_reviewer_name');
        $config->review_summary = core()->getConfigData('catalog.products.review.summary') ?: 'review_counts';

        $config->compare_enabled = self::flag('catalog.products.settings.compare_option');
        $config->wishlist_enabled = self::flag('customer.settings.wishlist.wishlist_option');
        $config->image_search_enabled = self::flag('catalog.products.settings.image_search');
        $config->social_share_enabled = self::flag('catalog.products.social_share.enabled');
        $config->products_per_page = app(Toolbar::class)->getAvailableLimits()->all();
        $config->listing_mode = core()->getConfigData('catalog.products.storefront.mode') ?: 'grid';
        $config->default_sort_by = core()->getConfigData('catalog.products.storefront.sort_by') ?: 'price-desc';

        $config->email_verification_required = self::flag('customer.settings.email.verification');
        $config->newsletter_enabled = self::flag('customer.settings.newsletter.subscription');
        $config->captcha_enabled = self::flag('customer.captcha.credentials.status');
        $config->social_login_providers = self::socialLoginProviders();

        $config->tax_cart_display_prices = self::taxDisplay('sales.taxes.shopping_cart.display_prices');
        $config->tax_cart_display_subtotal = self::taxDisplay('sales.taxes.shopping_cart.display_subtotal');
        $config->tax_cart_display_shipping = self::taxDisplay('sales.taxes.shopping_cart.display_shipping_amount');
        $config->tax_cart_show_breakdown = self::flag('sales.taxes.shopping_cart.show_tax_breakdown');
        $config->tax_order_display_prices = self::taxDisplay('sales.taxes.sales.display_prices');
        $config->tax_order_display_subtotal = self::taxDisplay('sales.taxes.sales.display_subtotal');
        $config->tax_order_display_shipping = self::taxDisplay('sales.taxes.sales.display_shipping_amount');

        $config->gdpr_enabled = self::flag('general.gdpr.settings.enabled');
        $config->eu_withdrawal_enabled = self::flag('sales.eu_withdrawal.general.enabled');

        return $config;
    }

    /**
     * A boolean setting of the requested channel and locale.
     */
    private static function flag(string $code): bool
    {
        return (bool) core()->getConfigData($code);
    }

    /**
     * How a tax-display setting shows prices, falling back to core's default.
     */
    private static function taxDisplay(string $code): string
    {
        return core()->getConfigData($code) ?: 'excluding_tax';
    }

    /**
     * The social login providers that are switched on and that the API can sign a customer in with.
     *
     * @return array<int, string>
     */
    private static function socialLoginProviders(): array
    {
        return collect(SocialLoginTokenVerifier::PROVIDERS)
            ->filter(fn (array $provider) => (bool) core()->getConfigData('customer.settings.social_login.'.$provider['enable']))
            ->keys()
            ->values()
            ->all();
    }
}
