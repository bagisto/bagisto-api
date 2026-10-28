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
            adminReorderEnabled
            cartSummary
            minimumOrderEnabled
            minimumOrderAmount
            minimumOrderIncludesDiscount
            minimumOrderIncludesTax
            minimumOrderMessage
            orderNumberPrefix
            orderNumberLength
            orderNumberSuffix
            orderNumberGenerator
            orderMaxRetryAttempts
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
            buyNowButtonEnabled
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
            cartPageEnabled
            miniCartEnabled
            miniCartOfferInfo
            backordersEnabled
            relatedProductsLimit
            upSellProductsLimit
            crossSellProductsLimit
            searchMinQueryLength
            searchMaxQueryLength
            socialShareProviders
            socialShareMessage
            categoryView
            breadcrumbsEnabled
            headerOfferTitle
            headerOfferLinkTitle
            headerOfferLink
            footerCopyright
            captchaSiteKey
            signupNewsletterEnabled
            loginRedirectTo
            addressCountryRequired
            addressStateRequired
            addressPostcodeRequired
            addressStreetLines
            gdprAgreementEnabled
            gdprAgreementLabel
            gdprAgreementContent
            cookieConsentEnabled
            cookieConsentPosition
            cookieConsentDescription
            cookieConsentCategories
            returnWindowDays
            returnPolicy
            returnAllowedFileTypes
            returnAllowedProductTypes
            returnAfterCancelledAllowed
            returnAfterDeclinedAllowed
            weightUnit
            customCss
            customJavascript
            speculationRules
            sitemapEnabled
            contactName
            contactEmail
            richSnippets
            aiImageSearchEnabled
            aiReviewTranslationEnabled
            aiCheckoutMessageEnabled
            cookieConsentStaticBlockIdentifier
            taxCalculationBasedOn
            taxProductPrices
            taxShippingPrices
            taxShippingCategoryId
            taxProductCategoryId
            taxApplyOn
            taxDefaultCountry
            taxDefaultState
            taxDefaultPostcode
            storeInformation
          }
        }
    GQL;

    /**
     * Fields that are null when the store has not filled the setting in.
     */
    private const NULLABLE_FIELDS = [
        'minimumOrderMessage', 'orderNumberPrefix', 'orderNumberLength', 'orderNumberSuffix', 'orderNumberGenerator', 'miniCartOfferInfo', 'relatedProductsLimit', 'upSellProductsLimit', 'crossSellProductsLimit',
        'socialShareMessage', 'headerOfferTitle', 'headerOfferLinkTitle', 'headerOfferLink', 'footerCopyright',
        'captchaSiteKey', 'gdprAgreementLabel', 'gdprAgreementContent', 'cookieConsentDescription', 'returnPolicy',
        'customCss', 'customJavascript', 'speculationRules', 'contactName', 'contactEmail', 'cookieConsentStaticBlockIdentifier',
        'taxDefaultCountry', 'taxDefaultState', 'taxDefaultPostcode', 'taxShippingCategoryId', 'taxProductCategoryId',
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
        $response = $this->graphQL(self::QUERY);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        return $response->json('data.storeConfig');
    }

    public function test_every_field_resolves(): void
    {
        $this->seedRequiredData();

        $config = $this->storeConfig();

        expect($config)->toHaveCount(103);
        expect($config['channel'])->toBe(core()->getCurrentChannel()->code);
        expect($config['locale'])->toBe(app()->getLocale());

        foreach ($config as $field => $value) {
            if (! in_array($field, self::NULLABLE_FIELDS, true)) {
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
        $this->setConfig('sales.order_settings.order_number.order_number_prefix', 'ORD-');
        $this->setConfig('sales.order_settings.order_number.order_number_length', '6');
        $this->setConfig('sales.order_settings.order_creation.max_retry_attempts', '5');
        $this->setConfig('sales.order_settings.reorder.admin', '0');
        $this->setConfig('catalog.products.review.guest_review', '1');
        $this->setConfig('sales.taxes.shopping_cart.display_subtotal', 'including_tax');
        $this->setConfig('catalog.products.storefront.buy_now_button_display', '1');
        $this->setConfig('sales.checkout.my_cart.summary', 'display_item_quantity');
        $this->setConfig('customer.address.information.street_lines', '2');
        $this->setConfig('sales.rma.setting.select_allowed_product_type', 'simple');
        $this->setConfig('general.gdpr.cookie.position', 'center');
        $this->setConfig('customer.settings.login_options.redirected_to_page', 'account');
        $this->setConfig('general.general.locale_options.weight_unit', 'lbs');
        $this->setConfig('sales.taxes.calculation.product_prices', 'including_tax');
        $this->setConfig('catalog.rich_snippets.products.show_sku', '1');
        $this->setConfig('sales.shipping.origin.store_name', 'Example Store');

        $config = $this->storeConfig();

        expect($config['guestCheckoutEnabled'])->toBeFalse();
        expect($config['minimumOrderEnabled'])->toBeTrue();
        expect((float) $config['minimumOrderAmount'])->toBe(75.0);
        expect($config['orderNumberPrefix'])->toBe('ORD-');
        expect($config['orderNumberLength'])->toBe(6);
        expect($config['orderMaxRetryAttempts'])->toBe(5);
        expect($config['adminReorderEnabled'])->toBeFalse();
        expect($config['guestReviewEnabled'])->toBeTrue();
        expect($config['taxCartDisplaySubtotal'])->toBe('including_tax');
        expect($config['buyNowButtonEnabled'])->toBeTrue();
        expect($config['cartSummary'])->toBe('display_item_quantity');
        expect($config['loginRedirectTo'])->toBe('account');
        expect($config['weightUnit'])->toBe('lbs');
        expect($config['taxProductPrices'])->toBe('including_tax');
        expect($config['richSnippets']['products']['showSku'])->toBeTrue();
        expect($config['storeInformation']['storeName'])->toBe('Example Store');
        expect($config['addressStreetLines'])->toBe(2);
        expect($config['returnAllowedProductTypes'])->toBe(['simple']);
        expect($config['cookieConsentPosition'])->toBe('center');
        expect($config['cookieConsentCategories'])->toHaveKey('strictly_necessary');
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
