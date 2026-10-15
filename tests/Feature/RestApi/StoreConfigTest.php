<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;

class StoreConfigTest extends RestApiTestCase
{
    private const FIELDS = [
        'id', 'channel', 'locale',
        'guestCheckoutEnabled', 'cartCrossSellEnabled', 'estimateShippingEnabled', 'reorderEnabled',
        'minimumOrderEnabled', 'minimumOrderAmount', 'minimumOrderIncludesDiscount', 'minimumOrderIncludesTax', 'minimumOrderMessage',
        'customerReviewEnabled', 'guestReviewEnabled', 'reviewerNameCensored', 'reviewSummary',
        'compareEnabled', 'wishlistEnabled', 'imageSearchEnabled', 'socialShareEnabled', 'productsPerPage', 'listingMode', 'defaultSortBy',
        'emailVerificationRequired', 'newsletterEnabled', 'captchaEnabled', 'socialLoginProviders',
        'taxCartDisplayPrices', 'taxCartDisplaySubtotal', 'taxCartDisplayShipping', 'taxCartShowBreakdown',
        'taxOrderDisplayPrices', 'taxOrderDisplaySubtotal', 'taxOrderDisplayShipping',
        'gdprEnabled', 'euWithdrawalEnabled',
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

    public function test_review_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('catalog.products.review.guest_review', '1');
        $this->setConfig('catalog.products.review.summary', 'star_counts');

        $config = $this->storeConfig();

        expect($config['guestReviewEnabled'])->toBeTrue();
        expect($config['reviewSummary'])->toBe('star_counts');
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

    public function test_secrets_are_never_returned(): void
    {
        $this->seedRequiredData();

        $this->setConfig('customer.settings.social_login.google_client_secret', 'super-secret');
        $this->setConfig('customer.captcha.credentials.api_key', 'captcha-secret');

        $body = $this->publicGet('/api/shop/store-config')->getContent();

        expect($body)->not->toContain('super-secret');
        expect($body)->not->toContain('captcha-secret');
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
