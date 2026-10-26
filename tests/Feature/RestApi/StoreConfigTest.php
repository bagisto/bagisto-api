<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;

class StoreConfigTest extends RestApiTestCase
{
    private const FIELDS = [
        'id', 'channel', 'locale',
        'guestCheckoutEnabled', 'cartCrossSellEnabled', 'estimateShippingEnabled', 'reorderEnabled', 'cartSummary',
        'minimumOrderEnabled', 'minimumOrderAmount', 'minimumOrderIncludesDiscount', 'minimumOrderIncludesTax', 'minimumOrderMessage',
        'orderNumberPrefix', 'orderNumberLength', 'orderNumberSuffix', 'orderNumberGenerator', 'orderMaxRetryAttempts', 'adminReorderEnabled',
        'customerReviewEnabled', 'guestReviewEnabled', 'reviewerNameCensored', 'reviewSummary',
        'compareEnabled', 'wishlistEnabled', 'imageSearchEnabled', 'socialShareEnabled', 'productsPerPage', 'listingMode', 'defaultSortBy', 'buyNowButtonEnabled',
        'emailVerificationRequired', 'newsletterEnabled', 'captchaEnabled', 'socialLoginProviders',
        'taxCartDisplayPrices', 'taxCartDisplaySubtotal', 'taxCartDisplayShipping', 'taxCartShowBreakdown',
        'taxOrderDisplayPrices', 'taxOrderDisplaySubtotal', 'taxOrderDisplayShipping',
        'gdprEnabled', 'euWithdrawalEnabled',
        'cartPageEnabled', 'miniCartEnabled', 'miniCartOfferInfo', 'backordersEnabled',
        'relatedProductsLimit', 'upSellProductsLimit', 'crossSellProductsLimit', 'searchMinQueryLength', 'searchMaxQueryLength',
        'socialShareProviders', 'socialShareMessage', 'categoryView', 'breadcrumbsEnabled',
        'headerOfferTitle', 'headerOfferLinkTitle', 'headerOfferLink', 'footerCopyright',
        'captchaSiteKey', 'signupNewsletterEnabled', 'loginRedirectTo', 'addressCountryRequired', 'addressStateRequired', 'addressPostcodeRequired', 'addressStreetLines',
        'gdprAgreementEnabled', 'gdprAgreementLabel', 'gdprAgreementContent',
        'cookieConsentEnabled', 'cookieConsentPosition', 'cookieConsentDescription', 'cookieConsentCategories',
        'returnWindowDays', 'returnPolicy', 'returnAllowedFileTypes', 'returnAllowedProductTypes', 'returnAfterCancelledAllowed', 'returnAfterDeclinedAllowed',
        'weightUnit', 'customCss', 'customJavascript', 'speculationRules', 'sitemapEnabled', 'contactName', 'contactEmail',
        'richSnippets', 'aiImageSearchEnabled', 'aiReviewTranslationEnabled', 'aiCheckoutMessageEnabled', 'cookieConsentStaticBlockIdentifier',
        'taxCalculationBasedOn', 'taxProductPrices', 'taxShippingPrices', 'taxApplyOn', 'taxDefaultCountry', 'taxDefaultState', 'taxDefaultPostcode',
        'storeInformation',
    ];

    private function setConfig(string $code, ?string $value): void
    {
        DB::table('core_config')->where('code', $code)->delete();

        if ($value !== null) {
            DB::table('core_config')->insert([
                'code' => $code,
                'value' => $value,
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => core()->getRequestedLocaleCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    private function storeConfig(): array
    {
        $response = $this->publicGet('/api/shop/store-config');

        $response->assertOk();

        expect($response->json())->toHaveCount(1);

        return $response->json('0');
    }

    public function test_returns_every_field_for_the_current_channel(): void
    {
        $this->seedRequiredData();

        $config = $this->storeConfig();

        expect(array_keys($config))->toEqualCanonicalizing(self::FIELDS);
        expect($config['channel'])->toBe(core()->getCurrentChannel()->code);
        expect($config['id'])->toBe($config['channel']);
        expect($config['locale'])->toBe(app()->getLocale());
    }

    public function test_channel_header_reads_another_channel(): void
    {
        $this->seedRequiredData();

        $channel = Channel::factory()->create();

        $response = $this->getJson('/api/shop/store-config', array_merge($this->storefrontHeaders(), [
            'X-Channel' => $channel->code,
        ]));

        $response->assertOk();
        expect($response->json('0.channel'))->toBe($channel->code);
        expect($response->json('0.id'))->toBe($channel->code);
    }

    public function test_guest_checkout_follows_the_store_setting(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.checkout.shopping_cart.allow_guest_checkout', '0');
        expect($this->storeConfig()['guestCheckoutEnabled'])->toBeFalse();

        $this->setConfig('sales.checkout.shopping_cart.allow_guest_checkout', '1');
        expect($this->storeConfig()['guestCheckoutEnabled'])->toBeTrue();
    }

    public function test_minimum_order_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.order_settings.minimum_order.enable', '1');
        $this->setConfig('sales.order_settings.minimum_order.minimum_order_amount', '50');
        $this->setConfig('sales.order_settings.minimum_order.include_tax_to_amount', '1');
        $this->setConfig('sales.order_settings.minimum_order.description', 'Orders must be at least 50.');

        $config = $this->storeConfig();

        expect($config['minimumOrderEnabled'])->toBeTrue();
        expect((float) $config['minimumOrderAmount'])->toBe(50.0);
        expect($config['minimumOrderIncludesTax'])->toBeTrue();
        expect($config['minimumOrderMessage'])->toBe('Orders must be at least 50.');
    }

    public function test_order_number_format_is_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.order_settings.order_number.order_number_prefix', 'ORD-');
        $this->setConfig('sales.order_settings.order_number.order_number_length', '6');
        $this->setConfig('sales.order_settings.order_number.order_number_suffix', '-EU');
        $this->setConfig('sales.order_settings.order_number.order_number_generator', 'App\\Generators\\OrderNumber');

        $config = $this->storeConfig();

        expect($config['orderNumberPrefix'])->toBe('ORD-');
        expect($config['orderNumberLength'])->toBe(6);
        expect($config['orderNumberSuffix'])->toBe('-EU');
        expect($config['orderNumberGenerator'])->toBe('App\\Generators\\OrderNumber');
    }

    public function test_order_creation_and_admin_reorder_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.order_settings.order_creation.max_retry_attempts', '5');
        $this->setConfig('sales.order_settings.reorder.admin', '0');

        $config = $this->storeConfig();

        expect($config['orderMaxRetryAttempts'])->toBe(5);
        expect($config['adminReorderEnabled'])->toBeFalse();
    }

    public function test_order_number_format_is_null_when_not_set(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.order_settings.order_number.order_number_prefix', null);
        $this->setConfig('sales.order_settings.order_number.order_number_length', null);
        $this->setConfig('sales.order_settings.order_number.order_number_suffix', null);
        $this->setConfig('sales.order_settings.order_number.order_number_generator', null);

        $config = $this->storeConfig();

        expect($config['orderNumberPrefix'])->toBeNull();
        expect($config['orderNumberLength'])->toBeNull();
        expect($config['orderNumberSuffix'])->toBeNull();
        expect($config['orderNumberGenerator'])->toBeNull();
    }

    public function test_review_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('catalog.products.review.guest_review', '1');
        $this->setConfig('catalog.products.review.summary', 'star_counts');

        $config = $this->storeConfig();

        expect($config['guestReviewEnabled'])->toBeTrue();
        expect($config['reviewSummary'])->toBe('star_counts');
    }

    public function test_cart_and_inventory_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.checkout.shopping_cart.cart_page', '0');
        $this->setConfig('sales.checkout.mini_cart.display_mini_cart', '1');
        $this->setConfig('sales.checkout.mini_cart.offer_info', 'Free gift today');
        $this->setConfig('catalog.inventory.stock_options.back_orders', '1');

        $config = $this->storeConfig();

        expect($config['cartPageEnabled'])->toBeFalse();
        expect($config['miniCartEnabled'])->toBeTrue();
        expect($config['miniCartOfferInfo'])->toBe('Free gift today');
        expect($config['backordersEnabled'])->toBeTrue();
    }

    public function test_product_page_and_search_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('catalog.products.product_view_page.no_of_related_products', '6');
        $this->setConfig('catalog.products.product_view_page.no_of_up_sells_products', null);
        $this->setConfig('catalog.products.cart_view_page.no_of_cross_sells_products', '3');
        $this->setConfig('catalog.products.search.min_query_length', '2');
        $this->setConfig('catalog.products.search.max_query_length', '120');

        $config = $this->storeConfig();

        expect($config['relatedProductsLimit'])->toBe(6);
        expect($config['upSellProductsLimit'])->toBeNull();
        expect($config['crossSellProductsLimit'])->toBe(3);
        expect($config['searchMinQueryLength'])->toBe(2);
        expect($config['searchMaxQueryLength'])->toBe(120);
    }

    public function test_social_share_providers_list_only_enabled_networks(): void
    {
        $this->seedRequiredData();

        $this->setConfig('catalog.products.social_share.enabled', '1');
        $this->setConfig('catalog.products.social_share.facebook', '1');
        $this->setConfig('catalog.products.social_share.twitter', '0');
        $this->setConfig('catalog.products.social_share.whatsapp', '1');
        $this->setConfig('catalog.products.social_share.share_message', 'Look at this');

        $config = $this->storeConfig();

        expect($config['socialShareProviders'])->toBe(['facebook', 'whatsapp']);
        expect($config['socialShareMessage'])->toBe('Look at this');

        $this->setConfig('catalog.products.social_share.enabled', '0');

        expect($this->storeConfig()['socialShareProviders'])->toBe([]);
    }

    public function test_header_footer_and_layout_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('general.content.header_offer.title', 'Summer sale');
        $this->setConfig('general.content.header_offer.redirection_title', 'Shop the sale');
        $this->setConfig('general.content.header_offer.redirection_link', 'https://example.com/sale');
        $this->setConfig('general.content.footer.copyright_content', 'Copyright Example');
        $this->setConfig('general.design.categories.category_view', 'sidebar');
        $this->setConfig('general.general.breadcrumbs.shop', '0');

        $config = $this->storeConfig();

        expect($config['headerOfferTitle'])->toBe('Summer sale');
        expect($config['headerOfferLinkTitle'])->toBe('Shop the sale');
        expect($config['headerOfferLink'])->toBe('https://example.com/sale');
        expect($config['footerCopyright'])->toBe('Copyright Example');
        expect($config['categoryView'])->toBe('sidebar');
        expect($config['breadcrumbsEnabled'])->toBeFalse();
    }

    public function test_customer_account_and_address_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('customer.captcha.credentials.site_key', 'public-site-key');
        $this->setConfig('customer.settings.create_new_account_options.news_letter', '0');
        $this->setConfig('customer.address.requirements.state', '0');
        $this->setConfig('customer.address.requirements.postcode', '0');
        $this->setConfig('customer.address.information.street_lines', '3');

        $config = $this->storeConfig();

        expect($config['captchaSiteKey'])->toBe('public-site-key');
        expect($config['signupNewsletterEnabled'])->toBeFalse();
        expect($config['addressCountryRequired'])->toBeTrue();
        expect($config['addressStateRequired'])->toBeFalse();
        expect($config['addressPostcodeRequired'])->toBeFalse();
        expect($config['addressStreetLines'])->toBe(3);
    }

    public function test_privacy_and_cookie_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('general.gdpr.agreement.enabled', '1');
        $this->setConfig('general.gdpr.agreement.agreement_label', 'I accept the privacy policy.');
        $this->setConfig('general.gdpr.cookie.enabled', '1');
        $this->setConfig('general.gdpr.cookie.position', 'top-right');
        $this->setConfig('general.gdpr.cookie_consent.measurements', 'We measure visits.');

        $config = $this->storeConfig();

        expect($config['gdprAgreementEnabled'])->toBeTrue();
        expect($config['gdprAgreementLabel'])->toBe('I accept the privacy policy.');
        expect($config['cookieConsentEnabled'])->toBeTrue();
        expect($config['cookieConsentPosition'])->toBe('top-right');
        expect(array_keys($config['cookieConsentCategories']))->toBe([
            'strictly_necessary', 'basic_interaction', 'experience_enhancement', 'measurements', 'targeting_advertising',
        ]);
        expect($config['cookieConsentCategories']['measurements'])->toBe('We measure visits.');
    }

    public function test_return_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.rma.setting.default_allow_days', '14');
        $this->setConfig('sales.rma.setting.return_policy', 'Return within 14 days.');
        $this->setConfig('sales.rma.setting.allowed_file_extension', 'image/png, image/webp');
        $this->setConfig('sales.rma.setting.select_allowed_product_type', 'simple,configurable');
        $this->setConfig('sales.rma.setting.allowed_new_rma_request_for_cancelled_request', 'no');
        $this->setConfig('sales.rma.setting.allowed_new_rma_request_for_declined_request', 'yes');

        $config = $this->storeConfig();

        expect($config['returnWindowDays'])->toBe(14);
        expect($config['returnPolicy'])->toBe('Return within 14 days.');
        expect($config['returnAllowedFileTypes'])->toBe(['image/png', 'image/webp']);
        expect($config['returnAllowedProductTypes'])->toBe(['simple', 'configurable']);
        expect($config['returnAfterCancelledAllowed'])->toBeFalse();
        expect($config['returnAfterDeclinedAllowed'])->toBeTrue();
    }

    public function test_cart_summary_follows_the_store_setting(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.checkout.my_cart.summary', null);
        expect($this->storeConfig()['cartSummary'])->toBe('display_number_of_items_in_cart');

        $this->setConfig('sales.checkout.my_cart.summary', 'display_item_quantity');
        expect($this->storeConfig()['cartSummary'])->toBe('display_item_quantity');
    }

    public function test_storefront_content_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('general.general.locale_options.weight_unit', 'lbs');
        $this->setConfig('general.content.custom_scripts.custom_css', 'body { color: red; }');
        $this->setConfig('general.content.custom_scripts.custom_javascript', 'console.log(1);');
        $this->setConfig('general.content.speculation_rules.enabled', '1');
        $this->setConfig('general.content.speculation_rules.prefetch_enabled', '1');
        $this->setConfig('general.content.speculation_rules.prerender_enabled', '0');
        $this->setConfig('general.sitemap.settings.enabled', '1');
        $this->setConfig('emails.configure.email_settings.contact_email', 'support@example.com');
        $this->setConfig('general.gdpr.cookie.static_block_identifier', 'cookie-block');

        $config = $this->storeConfig();

        expect($config['weightUnit'])->toBe('lbs');
        expect($config['customCss'])->toBe('body { color: red; }');
        expect($config['customJavascript'])->toBe('console.log(1);');
        expect($config['speculationRules'])->toHaveKey('prefetch');
        expect($config['speculationRules'])->not->toHaveKey('prerender');
        expect($config['sitemapEnabled'])->toBeTrue();
        expect($config['contactEmail'])->toBe('support@example.com');
        expect($config['cookieConsentStaticBlockIdentifier'])->toBe('cookie-block');
    }

    public function test_speculation_rules_are_null_when_switched_off(): void
    {
        $this->seedRequiredData();

        $this->setConfig('general.content.speculation_rules.enabled', '0');

        expect($this->storeConfig()['speculationRules'])->toBeNull();
    }

    public function test_rich_snippet_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('catalog.rich_snippets.products.enable', '1');
        $this->setConfig('catalog.rich_snippets.products.show_sku', '1');
        $this->setConfig('catalog.rich_snippets.products.show_weight', '0');
        $this->setConfig('catalog.rich_snippets.categories.enable', '0');

        $snippets = $this->storeConfig()['richSnippets'];

        expect(array_keys($snippets['products']))->toBe([
            'enabled', 'showSku', 'showWeight', 'showCategories', 'showImages', 'showReviews', 'showRatings', 'showOffers',
        ]);
        expect($snippets['products']['enabled'])->toBeTrue();
        expect($snippets['products']['showSku'])->toBeTrue();
        expect($snippets['products']['showWeight'])->toBeFalse();
        expect($snippets['categories']['enabled'])->toBeFalse();
    }

    public function test_ai_features_need_magic_ai_switched_on(): void
    {
        $this->seedRequiredData();

        $this->setConfig('magic_ai.storefront_features.image_search.enabled', '1');
        $this->setConfig('magic_ai.general.settings.enabled', '0');
        expect($this->storeConfig()['aiImageSearchEnabled'])->toBeFalse();

        $this->setConfig('magic_ai.general.settings.enabled', '1');
        expect($this->storeConfig()['aiImageSearchEnabled'])->toBeTrue();
    }

    public function test_tax_calculation_and_store_information_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.taxes.calculation.based_on', 'billing_address');
        $this->setConfig('sales.taxes.calculation.product_prices', 'including_tax');
        $this->setConfig('sales.taxes.calculation.apply_tax_on', 'before_discount');
        $this->setConfig('sales.taxes.default_destination_calculation.country', 'DE');
        $this->setConfig('sales.shipping.origin.store_name', 'Example Store');
        $this->setConfig('sales.shipping.origin.zipcode', '10115');

        $config = $this->storeConfig();

        expect($config['taxCalculationBasedOn'])->toBe('billing_address');
        expect($config['taxProductPrices'])->toBe('including_tax');
        expect($config['taxApplyOn'])->toBe('before_discount');
        expect($config['taxDefaultCountry'])->toBe('DE');
        expect(array_keys($config['storeInformation']))->toBe([
            'storeName', 'address', 'city', 'state', 'country', 'zipcode', 'vatNumber', 'contact', 'bankDetails',
        ]);
        expect($config['storeInformation']['storeName'])->toBe('Example Store');
        expect($config['storeInformation']['zipcode'])->toBe('10115');
    }

    public function test_secrets_are_never_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('customer.captcha.credentials.api_key', 'secret-captcha-key');
        $this->setConfig('emails.configure.smtp.password', 'secret-smtp-password');
        $this->setConfig('customer.settings.social_login.google_client_secret', 'secret-google-client');

        $body = json_encode($this->storeConfig());

        expect($body)->not->toContain('secret-captcha-key');
        expect($body)->not->toContain('secret-smtp-password');
        expect($body)->not->toContain('secret-google-client');
    }

    public function test_login_redirect_follows_the_store_setting(): void
    {
        $this->seedRequiredData();

        $this->setConfig('customer.settings.login_options.redirected_to_page', null);
        expect($this->storeConfig()['loginRedirectTo'])->toBe('home');

        $this->setConfig('customer.settings.login_options.redirected_to_page', 'account');
        expect($this->storeConfig()['loginRedirectTo'])->toBe('account');
    }

    public function test_buy_now_button_follows_the_store_setting(): void
    {
        $this->seedRequiredData();

        $this->setConfig('catalog.products.storefront.buy_now_button_display', '0');
        expect($this->storeConfig()['buyNowButtonEnabled'])->toBeFalse();

        $this->setConfig('catalog.products.storefront.buy_now_button_display', '1');
        expect($this->storeConfig()['buyNowButtonEnabled'])->toBeTrue();
    }

    public function test_products_per_page_is_a_list_of_integers(): void
    {
        $this->seedRequiredData();

        $this->setConfig('catalog.products.storefront.products_per_page', '9, 18,abc,18');

        expect($this->storeConfig()['productsPerPage'])->toBe([9, 18]);
    }

    public function test_products_per_page_falls_back_to_the_storefront_default(): void
    {
        $this->seedRequiredData();

        $this->setConfig('catalog.products.storefront.products_per_page', null);

        expect($this->storeConfig()['productsPerPage'])->toBe([12, 24, 36, 48]);
    }

    public function test_only_enabled_supported_social_login_providers_are_listed(): void
    {
        $this->seedRequiredData();

        $this->setConfig('customer.settings.social_login.enable_google', '1');
        $this->setConfig('customer.settings.social_login.enable_facebook', '0');
        $this->setConfig('customer.settings.social_login.enable_linkedin-openid', '1');
        $this->setConfig('customer.settings.social_login.enable_github', '1');

        expect($this->storeConfig()['socialLoginProviders'])->toBe(['google', 'linkedin']);
    }

    public function test_tax_display_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.taxes.shopping_cart.display_prices', 'both');
        $this->setConfig('sales.taxes.sales.display_shipping_amount', 'including_tax');

        $config = $this->storeConfig();

        expect($config['taxCartDisplayPrices'])->toBe('both');
        expect($config['taxOrderDisplayShipping'])->toBe('including_tax');
    }

    public function test_unknown_channel_header_is_rejected(): void
    {
        $this->seedRequiredData();

        $response = $this->getJson('/api/shop/store-config', array_merge($this->storefrontHeaders(), [
            'X-Channel' => 'no-such-channel',
        ]));

        $response->assertStatus(400);
    }
}
