<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Models\StoreConfig;
use Webkul\BagistoApi\Services\SocialLoginTokenVerifier;
use Webkul\Product\Helpers\Toolbar;

/**
 * Reads the public storefront settings of the current channel and locale from core configuration.
 */
class StoreConfigProvider implements ProviderInterface
{
    /**
     * Social networks the storefront can share a product to.
     */
    private const SOCIAL_SHARE_PROVIDERS = ['facebook', 'twitter', 'pinterest', 'whatsapp', 'linkedin', 'email'];

    /**
     * Cookie categories a shopper can accept or refuse.
     */
    private const COOKIE_CONSENT_CATEGORIES = [
        'strictly_necessary',
        'basic_interaction',
        'experience_enhancement',
        'measurements',
        'targeting_advertising',
    ];

    /**
     * Product rich-snippet blocks a store can switch on.
     */
    private const RICH_SNIPPET_PRODUCT_FIELDS = [
        'show_sku', 'show_weight', 'show_categories', 'show_images', 'show_reviews', 'show_ratings', 'show_offers',
    ];

    /**
     * Store address and contact settings printed on customer documents.
     */
    private const STORE_INFORMATION_FIELDS = [
        'store_name', 'address', 'city', 'state', 'country', 'zipcode', 'vat_number', 'contact', 'bank_details',
    ];

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
        $config->admin_reorder_enabled = self::flag('sales.order_settings.reorder.admin');
        $config->cart_summary = core()->getConfigData('sales.checkout.my_cart.summary') ?: 'display_number_of_items_in_cart';
        $config->cart_page_enabled = self::flag('sales.checkout.shopping_cart.cart_page');
        $config->mini_cart_enabled = self::flag('sales.checkout.mini_cart.display_mini_cart');
        $config->mini_cart_offer_info = self::text('sales.checkout.mini_cart.offer_info');
        $config->backorders_enabled = self::flag('catalog.inventory.stock_options.back_orders');

        $config->minimum_order_enabled = self::flag('sales.order_settings.minimum_order.enable');
        $config->minimum_order_amount = (float) (core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0);
        $config->minimum_order_includes_discount = self::flag('sales.order_settings.minimum_order.include_discount_amount');
        $config->minimum_order_includes_tax = self::flag('sales.order_settings.minimum_order.include_tax_to_amount');
        $config->minimum_order_message = core()->getConfigData('sales.order_settings.minimum_order.description') ?: null;

        $config->order_number_prefix = self::text('sales.order_settings.order_number.order_number_prefix');
        $config->order_number_length = self::number('sales.order_settings.order_number.order_number_length');
        $config->order_number_suffix = self::text('sales.order_settings.order_number.order_number_suffix');
        $config->order_number_generator = self::text('sales.order_settings.order_number.order_number_generator');
        $config->order_max_retry_attempts = self::number('sales.order_settings.order_creation.max_retry_attempts') ?? 3;

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
        $config->buy_now_button_enabled = self::flag('catalog.products.storefront.buy_now_button_display');
        $config->related_products_limit = self::number('catalog.products.product_view_page.no_of_related_products');
        $config->up_sell_products_limit = self::number('catalog.products.product_view_page.no_of_up_sells_products');
        $config->cross_sell_products_limit = self::number('catalog.products.cart_view_page.no_of_cross_sells_products');
        $config->search_min_query_length = self::number('catalog.products.search.min_query_length') ?? 0;
        $config->search_max_query_length = self::number('catalog.products.search.max_query_length') ?? 1000;
        $config->social_share_providers = self::socialShareProviders();
        $config->social_share_message = self::text('catalog.products.social_share.share_message');
        $config->category_view = core()->getConfigData('general.design.categories.category_view') ?: 'default';
        $config->breadcrumbs_enabled = self::flag('general.general.breadcrumbs.shop');
        $config->header_offer_title = self::text('general.content.header_offer.title');
        $config->header_offer_link_title = self::text('general.content.header_offer.redirection_title');
        $config->header_offer_link = self::text('general.content.header_offer.redirection_link');
        $config->footer_copyright = self::text('general.content.footer.copyright_content');
        $config->weight_unit = core()->getConfigData('general.general.locale_options.weight_unit') ?: 'kgs';
        $config->custom_css = self::text('general.content.custom_scripts.custom_css');
        $config->custom_javascript = self::text('general.content.custom_scripts.custom_javascript');
        $config->speculation_rules = self::flag('general.content.speculation_rules.enabled') ? core()->getSpeculationRules() : null;
        $config->sitemap_enabled = self::flag('general.sitemap.settings.enabled');
        $config->contact_name = self::text('emails.configure.email_settings.contact_name');
        $config->contact_email = self::text('emails.configure.email_settings.contact_email');
        $config->rich_snippets = self::richSnippets();
        $config->ai_image_search_enabled = self::aiFeature('image_search');
        $config->ai_review_translation_enabled = self::aiFeature('review_translation');
        $config->ai_checkout_message_enabled = self::aiFeature('checkout_message');

        $config->email_verification_required = self::flag('customer.settings.email.verification');
        $config->newsletter_enabled = self::flag('customer.settings.newsletter.subscription');
        $config->captcha_enabled = self::flag('customer.captcha.credentials.status');
        $config->captcha_site_key = self::text('customer.captcha.credentials.site_key');
        $config->signup_newsletter_enabled = self::flag('customer.settings.create_new_account_options.news_letter');
        $config->login_redirect_to = core()->getConfigData('customer.settings.login_options.redirected_to_page') ?: 'home';
        $config->address_country_required = self::flag('customer.address.requirements.country');
        $config->address_state_required = self::flag('customer.address.requirements.state');
        $config->address_postcode_required = self::flag('customer.address.requirements.postcode');
        $config->address_street_lines = max(1, self::number('customer.address.information.street_lines') ?? 1);
        $config->social_login_providers = self::socialLoginProviders();

        $config->tax_cart_display_prices = self::taxDisplay('sales.taxes.shopping_cart.display_prices');
        $config->tax_cart_display_subtotal = self::taxDisplay('sales.taxes.shopping_cart.display_subtotal');
        $config->tax_cart_display_shipping = self::taxDisplay('sales.taxes.shopping_cart.display_shipping_amount');
        $config->tax_cart_show_breakdown = self::flag('sales.taxes.shopping_cart.show_tax_breakdown');
        $config->tax_order_display_prices = self::taxDisplay('sales.taxes.sales.display_prices');
        $config->tax_order_display_subtotal = self::taxDisplay('sales.taxes.sales.display_subtotal');
        $config->tax_order_display_shipping = self::taxDisplay('sales.taxes.sales.display_shipping_amount');
        $config->tax_calculation_based_on = core()->getConfigData('sales.taxes.calculation.based_on') ?: 'shipping_address';
        $config->tax_product_prices = self::taxDisplay('sales.taxes.calculation.product_prices');
        $config->tax_shipping_prices = self::taxDisplay('sales.taxes.calculation.shipping_prices');
        $config->tax_shipping_category_id = self::number('sales.taxes.categories.shipping') ?: null;
        $config->tax_product_category_id = self::number('sales.taxes.categories.product') ?: null;
        $config->tax_apply_on = core()->getConfigData('sales.taxes.calculation.apply_tax_on') ?: 'after_discount';
        $config->tax_default_country = self::text('sales.taxes.default_destination_calculation.country');
        $config->tax_default_state = self::text('sales.taxes.default_destination_calculation.state');
        $config->tax_default_postcode = self::text('sales.taxes.default_destination_calculation.post_code');
        $config->store_information = self::storeInformation();

        $config->gdpr_enabled = self::flag('general.gdpr.settings.enabled');
        $config->gdpr_agreement_enabled = self::flag('general.gdpr.agreement.enabled');
        $config->gdpr_agreement_label = self::text('general.gdpr.agreement.agreement_label');
        $config->gdpr_agreement_content = self::text('general.gdpr.agreement.agreement_content');
        $config->cookie_consent_enabled = self::flag('general.gdpr.cookie.enabled');
        $config->cookie_consent_position = core()->getConfigData('general.gdpr.cookie.position') ?: 'bottom-left';
        $config->cookie_consent_description = self::text('general.gdpr.cookie.description');
        $config->cookie_consent_static_block_identifier = self::text('general.gdpr.cookie.static_block_identifier');
        $config->cookie_consent_categories = collect(self::COOKIE_CONSENT_CATEGORIES)
            ->mapWithKeys(fn (string $category) => [$category => self::text('general.gdpr.cookie_consent.'.$category)])
            ->all();

        $config->eu_withdrawal_enabled = self::flag('sales.eu_withdrawal.general.enabled');

        $config->return_window_days = self::number('sales.rma.setting.default_allow_days') ?? 0;
        $config->return_policy = self::text('sales.rma.setting.return_policy');
        $config->return_allowed_file_types = self::list('sales.rma.setting.allowed_file_extension');
        $config->return_allowed_product_types = self::list('sales.rma.setting.select_allowed_product_type');
        $config->return_after_cancelled_allowed = core()->getConfigData('sales.rma.setting.allowed_new_rma_request_for_cancelled_request') === 'yes';
        $config->return_after_declined_allowed = core()->getConfigData('sales.rma.setting.allowed_new_rma_request_for_declined_request') === 'yes';

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
     * A text setting, or null when it is empty.
     */
    private static function text(string $code): ?string
    {
        $value = core()->getConfigData($code);

        return $value === null || $value === '' ? null : (string) $value;
    }

    /**
     * A numeric setting, or null when it is empty or not a number.
     */
    private static function number(string $code): ?int
    {
        $value = core()->getConfigData($code);

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * A comma-separated multiselect setting as a list.
     *
     * @return array<int, string>
     */
    private static function list(string $code): array
    {
        return collect(explode(',', (string) core()->getConfigData($code)))
            ->map(fn (string $value) => trim($value))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * The social networks a product can be shared to, empty when sharing is off.
     *
     * @return array<int, string>
     */
    private static function socialShareProviders(): array
    {
        if (! self::flag('catalog.products.social_share.enabled')) {
            return [];
        }

        return collect(self::SOCIAL_SHARE_PROVIDERS)
            ->filter(fn (string $provider) => self::flag('catalog.products.social_share.'.$provider))
            ->values()
            ->all();
    }

    /**
     * Which structured-data blocks the storefront prints on product and category pages.
     *
     * @return array<string, array<string, bool>>
     */
    private static function richSnippets(): array
    {
        $products = collect(self::RICH_SNIPPET_PRODUCT_FIELDS)
            ->mapWithKeys(fn (string $field) => [Str::camel($field) => self::flag('catalog.rich_snippets.products.'.$field)])
            ->all();

        return [
            'products' => ['enabled' => self::flag('catalog.rich_snippets.products.enable')] + $products,
            'categories' => [
                'enabled' => self::flag('catalog.rich_snippets.categories.enable'),
                'showSearchInputField' => self::flag('catalog.rich_snippets.categories.show_search_input_field'),
            ],
        ];
    }

    /**
     * Whether a Magic AI storefront feature is on, which also needs Magic AI itself to be on.
     */
    private static function aiFeature(string $feature): bool
    {
        return self::flag('magic_ai.general.settings.enabled')
            && self::flag('magic_ai.storefront_features.'.$feature.'.enabled');
    }

    /**
     * The store address and contact details printed on customer documents.
     *
     * @return array<string, string|null>
     */
    private static function storeInformation(): array
    {
        return collect(self::STORE_INFORMATION_FIELDS)
            ->mapWithKeys(fn (string $field) => [Str::camel($field) => self::text('sales.shipping.origin.'.$field)])
            ->all();
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
