<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class StoreConfigTest extends GraphQLTestCase
{
    /**
     * The query constant.
     */
    private const QUERY = <<<'GQL'
        query {
          storeConfig {
            channel
            locale
            guestCheckoutEnabled
            minimumOrderEnabled
            minimumOrderAmount
            minimumOrderMessage
            customerReviewEnabled
            guestReviewEnabled
            compareEnabled
            wishlistEnabled
            buyNowEnabled
            socialLoginProviders
            taxCartDisplayPrices
            emailVerificationRequired
            gdprEnabled
            euWithdrawalEnabled
          }
        }
    GQL;

    /**
     * Test returns the settings of the current channel.
     */
    public function test_returns_the_settings_of_the_current_channel(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL(self::QUERY);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.storeConfig.channel'))->toBe(core()->getCurrentChannel()->code);
        expect($response->json('data.storeConfig.guestCheckoutEnabled'))->toBeBool();
        expect($response->json('data.storeConfig.socialLoginProviders'))->toBeArray();
    }

    /**
     * Test guest checkout follows the store setting.
     */
    public function test_guest_checkout_follows_the_store_setting(): void
    {
        $this->seedRequiredData();

        $this->storeSetting('sales.checkout.shopping_cart.allow_guest_checkout', '0');
        expect($this->graphQL(self::QUERY)->json('data.storeConfig.guestCheckoutEnabled'))->toBeFalse();

        $this->storeSetting('sales.checkout.shopping_cart.allow_guest_checkout', '1');
        expect($this->graphQL(self::QUERY)->json('data.storeConfig.guestCheckoutEnabled'))->toBeTrue();
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

        $data = $this->graphQL(self::QUERY)->json('data.storeConfig');

        expect($data['minimumOrderEnabled'])->toBeTrue();
        expect($data['minimumOrderAmount'])->toBe(25.5);
        expect($data['minimumOrderMessage'])->toBe('Minimum is 25.5');
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
        ] as $provider => $value) {
            $this->storeSetting('customer.settings.social_login.enable_'.$provider, $value);
            $this->storeSetting('customer.settings.social_login.'.$provider.'_client_id', 'id-'.$provider);
        }

        expect($this->graphQL(self::QUERY)->json('data.storeConfig.socialLoginProviders'))->toBe(['google', 'linkedin']);
    }

    /**
     * Test review and tax settings are returned.
     */
    public function test_review_and_tax_settings_are_returned(): void
    {
        $this->seedRequiredData();

        $this->storeSetting('catalog.products.review.guest_review', '1');
        $this->storeSetting('sales.taxes.shopping_cart.display_prices', 'including_tax');

        $data = $this->graphQL(self::QUERY)->json('data.storeConfig');

        expect($data['guestReviewEnabled'])->toBeTrue();
        expect($data['taxCartDisplayPrices'])->toBe('including_tax');
    }

    /**
     * Test secret settings are not queryable.
     */
    public function test_secret_settings_are_not_queryable(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL('query { storeConfig { googleClientSecret } }');

        expect($response->json('errors'))->not->toBeNull();
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
