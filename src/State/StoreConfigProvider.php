<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\EuWithdrawalStatus;
use Webkul\BagistoApi\Models\GdprStatus;
use Webkul\BagistoApi\Models\StoreConfig;

/**
 * The non-secret store settings of the current channel, read from the admin configuration.
 *
 * Every value comes from an explicit allow-list of keys. Credentials, secrets and callback
 * URLs are never read here.
 */
class StoreConfigProvider implements ProviderInterface
{
    /**
     * Social login providers the API can sign a customer in with. A provider is listed only
     * when it is switched on and its client id is saved; the id itself is never returned.
     */
    private const SOCIAL_LOGIN_PROVIDERS = ['google', 'facebook', 'linkedin'];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [self::build()];
    }

    public static function build(): StoreConfig
    {
        $channel = core()->getCurrentChannel();

        $config = new StoreConfig;

        $config->id = $channel->code;
        $config->channel = $channel->code;
        $config->locale = app()->getLocale();

        self::fillCheckout($config);
        self::fillReviews($config);
        self::fillCatalog($config);
        self::fillCustomer($config);
        self::fillTax($config);

        $config->gdpr_enabled = (bool) FeatureStatusProvider::build(GdprStatus::class)->enabled;
        $config->eu_withdrawal_enabled = (bool) FeatureStatusProvider::build(EuWithdrawalStatus::class)->enabled;

        return $config;
    }

    private static function fillCheckout(StoreConfig $config): void
    {
        $config->guest_checkout_enabled = self::flag('sales.checkout.shopping_cart.allow_guest_checkout');
        $config->cart_cross_sell_enabled = self::flag('sales.checkout.shopping_cart.cross_sell');
        $config->estimate_shipping_enabled = self::flag('sales.checkout.shopping_cart.estimate_shipping');
        $config->reorder_enabled = self::flag('sales.order_settings.reorder.shop');

        $config->minimum_order_enabled = self::flag('sales.order_settings.minimum_order.enable');
        $config->minimum_order_amount = self::number('sales.order_settings.minimum_order.minimum_order_amount');
        $config->minimum_order_includes_discount = self::flag('sales.order_settings.minimum_order.include_discount_amount');
        $config->minimum_order_includes_tax = self::flag('sales.order_settings.minimum_order.include_tax_to_amount');
        $config->minimum_order_message = self::text('sales.order_settings.minimum_order.description');
    }

    private static function fillReviews(StoreConfig $config): void
    {
        $config->customer_review_enabled = self::flag('catalog.products.review.customer_review');
        $config->guest_review_enabled = self::flag('catalog.products.review.guest_review');
        $config->reviewer_name_censored = self::flag('catalog.products.review.censoring_reviewer_name');
        $config->review_summary = self::text('catalog.products.review.summary');
    }

    private static function fillCatalog(StoreConfig $config): void
    {
        $config->compare_enabled = self::flag('catalog.products.settings.compare_option');
        $config->image_search_enabled = self::flag('catalog.products.settings.image_search');
        $config->wishlist_enabled = self::flag('customer.settings.wishlist.wishlist_option');
        $config->social_share_enabled = self::flag('catalog.products.social_share.enabled');

        $perPage = self::number('catalog.products.storefront.products_per_page');

        $config->products_per_page = $perPage === null ? null : (int) $perPage;
        $config->listing_mode = self::text('catalog.products.storefront.mode');
        $config->default_sort_by = self::text('catalog.products.storefront.sort_by');
    }

    private static function fillCustomer(StoreConfig $config): void
    {
        $config->email_verification_required = self::flag('customer.settings.email.verification');
        $config->newsletter_enabled = self::flag('customer.settings.newsletter.subscription');
        $config->captcha_enabled = self::flag('customer.captcha.credentials.status');

        $config->social_login_providers = array_values(array_filter(
            self::SOCIAL_LOGIN_PROVIDERS,
            fn (string $provider): bool => self::flag('customer.settings.social_login.enable_'.$provider)
                && self::text('customer.settings.social_login.'.$provider.'_client_id') !== null,
        ));
    }

    private static function fillTax(StoreConfig $config): void
    {
        $config->tax_cart_display_prices = self::text('sales.taxes.shopping_cart.display_prices');
        $config->tax_cart_display_subtotal = self::text('sales.taxes.shopping_cart.display_subtotal');
        $config->tax_cart_display_shipping = self::text('sales.taxes.shopping_cart.display_shipping_amount');
        $config->tax_cart_show_breakdown = self::flag('sales.taxes.shopping_cart.show_tax_breakdown');

        $config->tax_order_display_prices = self::text('sales.taxes.sales.display_prices');
        $config->tax_order_display_subtotal = self::text('sales.taxes.sales.display_subtotal');
        $config->tax_order_display_shipping = self::text('sales.taxes.sales.display_shipping_amount');
    }

    private static function flag(string $code): bool
    {
        return filter_var(core()->getConfigData($code), FILTER_VALIDATE_BOOLEAN);
    }

    private static function number(string $code): ?float
    {
        $value = core()->getConfigData($code);

        return is_numeric($value) ? (float) $value : null;
    }

    private static function text(string $code): ?string
    {
        $value = core()->getConfigData($code);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
