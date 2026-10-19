<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class StoreConfigTest extends RestApiTestCase
{
    /**
     * The url.
     */
    private string $url = '/api/shop/store-config';

    /**
     * Test returns one settings object for the current channel.
     */
    public function test_returns_one_settings_object_for_the_current_channel(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();

        $body = $response->json();

        expect($body)->toBeArray();
        expect(count($body))->toBe(1);
        expect($body[0]['channel'])->toBe(core()->getCurrentChannel()->code);
        expect($body[0])->toHaveKeys([
            'guestCheckoutEnabled', 'minimumOrderEnabled', 'minimumOrderAmount', 'customerReviewEnabled',
            'guestReviewEnabled', 'compareEnabled', 'wishlistEnabled', 'buyNowEnabled', 'socialLoginProviders',
            'taxCartDisplayPrices', 'emailVerificationRequired', 'gdprEnabled', 'euWithdrawalEnabled',
        ]);
    }

    /**
     * Test requires a storefront key.
     */
    public function test_requires_a_storefront_key(): void
    {
        $this->seedRequiredData();

        $this->withoutHeader('X-STOREFRONT-KEY');

        expect($this->publicGet($this->url)->getStatusCode())->toBeIn([200, 401, 403]);
    }

    /**
     * Test guest checkout follows the store setting.
     */
    public function test_guest_checkout_follows_the_store_setting(): void
    {
        $this->seedRequiredData();

        $this->storeSetting('sales.checkout.shopping_cart.allow_guest_checkout', '0');
        expect($this->publicGet($this->url)->json('0.guestCheckoutEnabled'))->toBeFalse();

        $this->storeSetting('sales.checkout.shopping_cart.allow_guest_checkout', '1');
        expect($this->publicGet($this->url)->json('0.guestCheckoutEnabled'))->toBeTrue();
    }

    /**
     * Test buy now follows the store setting.
     */
    public function test_buy_now_follows_the_store_setting(): void
    {
        $this->seedRequiredData();

        $this->storeSetting('catalog.products.product_view_page.buy_now_button_display', '0');
        expect($this->publicGet($this->url)->json('0.buyNowEnabled'))->toBeFalse();

        $this->storeSetting('catalog.products.product_view_page.buy_now_button_display', '1');
        expect($this->publicGet($this->url)->json('0.buyNowEnabled'))->toBeTrue();
    }

    /**
     * Test minimum order values are returned.
     */
    public function test_minimum_order_values_are_returned(): void
    {
        $this->seedRequiredData();

        $this->storeSetting('sales.order_settings.minimum_order.enable', '1');
        $this->storeSetting('sales.order_settings.minimum_order.minimum_order_amount', '25.5');
        $this->storeSetting('sales.order_settings.minimum_order.description', 'Minimum is 25.5');

        $body = $this->publicGet($this->url)->json('0');

        expect($body['minimumOrderEnabled'])->toBeTrue();
        expect($body['minimumOrderAmount'])->toBe(25.5);
        expect($body['minimumOrderMessage'])->toBe('Minimum is 25.5');
    }

    /**
     * Test review rules are returned.
     */
    public function test_review_rules_are_returned(): void
    {
        $this->seedRequiredData();

        $this->storeSetting('catalog.products.review.guest_review', '1');
        $this->storeSetting('catalog.products.review.customer_review', '0');

        $body = $this->publicGet($this->url)->json('0');

        expect($body['guestReviewEnabled'])->toBeTrue();
        expect($body['customerReviewEnabled'])->toBeFalse();
    }

    /**
     * Test only enabled social login providers are listed.
     */
    public function test_only_enabled_social_login_providers_are_listed(): void
    {
        $this->seedRequiredData();

        foreach ([
            'google' => '1',
            'facebook' => '0',
            'linkedin' => '1',
            'github' => '1',
        ] as $provider => $value) {
            $this->storeSetting('customer.settings.social_login.enable_'.$provider, $value);
            $this->storeSetting('customer.settings.social_login.'.$provider.'_client_id', 'id-'.$provider);
        }

        $providers = $this->publicGet($this->url)->json('0.socialLoginProviders');

        expect($providers)->toBe(['google', 'linkedin']);
    }

    /**
     * Test tax display settings are returned.
     */
    public function test_tax_display_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->storeSetting('sales.taxes.shopping_cart.display_prices', 'both');

        expect($this->publicGet($this->url)->json('0.taxCartDisplayPrices'))->toBe('both');
    }

    /**
     * Test no secret is exposed.
     */
    public function test_no_secret_is_exposed(): void
    {
        $this->seedRequiredData();

        $this->storeSetting('customer.settings.social_login.google_client_secret', 'super-secret-value');
        $this->storeSetting('customer.captcha.credentials.api_key', 'captcha-secret-key');

        $json = $this->publicGet($this->url)->getContent();

        expect($json)->not->toContain('super-secret-value');
        expect($json)->not->toContain('captcha-secret-key');
        expect($json)->not->toContain('client_secret');
        expect($json)->not->toContain('api_key');
    }

    /**
     * Test a provider without a client id is not listed.
     */
    public function test_a_provider_without_a_client_id_is_not_listed(): void
    {
        $this->seedRequiredData();

        $this->storeSetting('customer.settings.social_login.enable_google', '1');
        $this->storeSetting('customer.settings.social_login.google_client_id', null);

        expect($this->publicGet($this->url)->json('0.socialLoginProviders'))->not->toContain('google');
    }

    /**
     * Store setting.
     */
    private function storeSetting(string $code, ?string $value): void
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
}
