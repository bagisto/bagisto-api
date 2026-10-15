<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class StoreConfigTest extends GraphQLTestCase
{
    private const QUERY = <<<'GQL'
        query {
          storeConfig {
            id
            channel
            locale
            guestCheckoutEnabled
            cartCrossSellEnabled
            estimateShippingEnabled
            reorderEnabled
            minimumOrderEnabled
            minimumOrderAmount
            minimumOrderIncludesDiscount
            minimumOrderIncludesTax
            minimumOrderMessage
            customerReviewEnabled
            guestReviewEnabled
            reviewerNameCensored
            reviewSummary
            compareEnabled
            wishlistEnabled
            imageSearchEnabled
            socialShareEnabled
            productsPerPage
            listingMode
            defaultSortBy
            emailVerificationRequired
            newsletterEnabled
            captchaEnabled
            socialLoginProviders
            taxCartDisplayPrices
            taxCartDisplaySubtotal
            taxCartDisplayShipping
            taxCartShowBreakdown
            taxOrderDisplayPrices
            taxOrderDisplaySubtotal
            taxOrderDisplayShipping
            gdprEnabled
            euWithdrawalEnabled
          }
        }
    GQL;

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
        $response = $this->graphQL(self::QUERY);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        return $response->json('data.storeConfig');
    }

    public function test_every_field_resolves(): void
    {
        $this->seedRequiredData();

        $config = $this->storeConfig();

        expect($config)->toHaveCount(36);
        expect($config['channel'])->toBe(core()->getCurrentChannel()->code);
        expect($config['locale'])->toBe(app()->getLocale());

        foreach ($config as $field => $value) {
            if ($field !== 'minimumOrderMessage') {
                expect($value)->not->toBeNull("{$field} resolved null");
            }
        }
    }

    public function test_settings_follow_the_store_configuration(): void
    {
        $this->seedRequiredData();

        $this->setConfig('sales.checkout.shopping_cart.allow_guest_checkout', '0');
        $this->setConfig('sales.order_settings.minimum_order.enable', '1');
        $this->setConfig('sales.order_settings.minimum_order.minimum_order_amount', '75');
        $this->setConfig('catalog.products.review.guest_review', '1');
        $this->setConfig('sales.taxes.shopping_cart.display_subtotal', 'including_tax');

        $config = $this->storeConfig();

        expect($config['guestCheckoutEnabled'])->toBeFalse();
        expect($config['minimumOrderEnabled'])->toBeTrue();
        expect((float) $config['minimumOrderAmount'])->toBe(75.0);
        expect($config['guestReviewEnabled'])->toBeTrue();
        expect($config['taxCartDisplaySubtotal'])->toBe('including_tax');
    }

    public function test_social_login_providers_lists_only_enabled_supported_providers(): void
    {
        $this->seedRequiredData();

        $this->setConfig('customer.settings.social_login.enable_google', '0');
        $this->setConfig('customer.settings.social_login.enable_facebook', '1');
        $this->setConfig('customer.settings.social_login.enable_linkedin-openid', '0');
        $this->setConfig('customer.settings.social_login.enable_github', '1');

        expect($this->storeConfig()['socialLoginProviders'])->toBe(['facebook']);
    }
}
