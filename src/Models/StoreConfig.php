<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\StoreConfigQueryResolver;
use Webkul\BagistoApi\State\StoreConfigProvider;

/**
 * The public storefront settings of the current channel and locale.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'StoreConfig',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/store-config',
            provider: StoreConfigProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['Store Config'],
                summary: 'Get the storefront settings of the current channel',
                description: 'Returns the store settings a storefront needs before it renders a page: checkout and minimum order rules, the order number format and order creation retries, review rules, product listing and product page options, header and footer content, custom CSS and JavaScript, speculation rules, rich snippets, customer account, login and address rules, tax display and calculation, store information, privacy and cookie consent, returns and optional features. Secrets such as API keys, client secrets and mail credentials are never returned. Only non-secret values are returned. Send `X-Channel` or `X-Locale` to read another channel or language. Public endpoint — the storefront key is enough.',
                parameters: [
                    new Parameter(
                        name: 'X-Channel',
                        in: 'header',
                        description: 'Channel code to read. Defaults to the channel of the request host.',
                        required: false,
                        schema: ['type' => 'string', 'example' => 'default'],
                    ),
                    new Parameter(
                        name: 'X-Locale',
                        in: 'header',
                        description: 'Locale code to read. Must be a locale of the channel.',
                        required: false,
                        schema: ['type' => 'string', 'example' => 'en'],
                    ),
                ],
                responses: [
                    '200' => new Response(
                        description: 'The storefront settings.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 'default',
                                        'channel' => 'default',
                                        'locale' => 'en',
                                        'guestCheckoutEnabled' => true,
                                        'cartCrossSellEnabled' => true,
                                        'estimateShippingEnabled' => true,
                                        'reorderEnabled' => true,
                                        'adminReorderEnabled' => true,
                                        'cartSummary' => 'display_number_of_items_in_cart',
                                        'cartPageEnabled' => true,
                                        'miniCartEnabled' => true,
                                        'miniCartOfferInfo' => 'Get Up To 30% OFF on your 1st order',
                                        'backordersEnabled' => false,
                                        'minimumOrderEnabled' => true,
                                        'minimumOrderAmount' => 50.0,
                                        'minimumOrderIncludesDiscount' => false,
                                        'minimumOrderIncludesTax' => false,
                                        'minimumOrderMessage' => 'Orders must be at least $50.',
                                        'orderNumberPrefix' => 'ORD-',
                                        'orderNumberLength' => 6,
                                        'orderNumberSuffix' => null,
                                        'orderNumberGenerator' => null,
                                        'orderMaxRetryAttempts' => 3,
                                        'customerReviewEnabled' => true,
                                        'guestReviewEnabled' => false,
                                        'reviewerNameCensored' => true,
                                        'reviewSummary' => 'review_counts',
                                        'compareEnabled' => true,
                                        'wishlistEnabled' => true,
                                        'imageSearchEnabled' => true,
                                        'socialShareEnabled' => false,
                                        'productsPerPage' => [12, 24, 36, 48],
                                        'listingMode' => 'grid',
                                        'defaultSortBy' => 'price-desc',
                                        'buyNowButtonEnabled' => true,
                                        'relatedProductsLimit' => 4,
                                        'upSellProductsLimit' => 4,
                                        'crossSellProductsLimit' => 4,
                                        'searchMinQueryLength' => 0,
                                        'searchMaxQueryLength' => 1000,
                                        'socialShareProviders' => ['facebook', 'whatsapp'],
                                        'socialShareMessage' => 'Check out this product',
                                        'categoryView' => 'default',
                                        'breadcrumbsEnabled' => true,
                                        'headerOfferTitle' => 'Get UPTO 40% OFF on your 1st order',
                                        'headerOfferLinkTitle' => 'SHOP NOW',
                                        'headerOfferLink' => 'https://example.com/sale',
                                        'footerCopyright' => '© 2026 Example Store',
                                        'weightUnit' => 'kgs',
                                        'customCss' => null,
                                        'customJavascript' => null,
                                        'speculationRules' => null,
                                        'sitemapEnabled' => true,
                                        'contactName' => 'Example Store',
                                        'contactEmail' => 'support@example.com',
                                        'richSnippets' => [
                                            'products' => [
                                                'enabled' => true,
                                                'showSku' => true,
                                                'showWeight' => false,
                                                'showCategories' => true,
                                                'showImages' => true,
                                                'showReviews' => true,
                                                'showRatings' => true,
                                                'showOffers' => true,
                                            ],
                                            'categories' => [
                                                'enabled' => true,
                                                'showSearchInputField' => false,
                                            ],
                                        ],
                                        'aiImageSearchEnabled' => false,
                                        'aiReviewTranslationEnabled' => false,
                                        'aiCheckoutMessageEnabled' => false,
                                        'emailVerificationRequired' => false,
                                        'newsletterEnabled' => true,
                                        'captchaEnabled' => false,
                                        'captchaSiteKey' => null,
                                        'signupNewsletterEnabled' => true,
                                        'loginRedirectTo' => 'home',
                                        'addressCountryRequired' => true,
                                        'addressStateRequired' => true,
                                        'addressPostcodeRequired' => true,
                                        'addressStreetLines' => 1,
                                        'socialLoginProviders' => ['google', 'facebook'],
                                        'taxCartDisplayPrices' => 'excluding_tax',
                                        'taxCartDisplaySubtotal' => 'excluding_tax',
                                        'taxCartDisplayShipping' => 'excluding_tax',
                                        'taxCartShowBreakdown' => false,
                                        'taxOrderDisplayPrices' => 'excluding_tax',
                                        'taxOrderDisplaySubtotal' => 'excluding_tax',
                                        'taxOrderDisplayShipping' => 'excluding_tax',
                                        'taxCalculationBasedOn' => 'shipping_address',
                                        'taxProductPrices' => 'excluding_tax',
                                        'taxShippingPrices' => 'excluding_tax',
                                        'taxShippingCategoryId' => null,
                                        'taxProductCategoryId' => 1,
                                        'taxApplyOn' => 'after_discount',
                                        'taxDefaultCountry' => 'US',
                                        'taxDefaultState' => 'CA',
                                        'taxDefaultPostcode' => null,
                                        'storeInformation' => [
                                            'storeName' => 'Example Store',
                                            'address' => '1 Market Street',
                                            'city' => 'San Francisco',
                                            'state' => 'CA',
                                            'country' => 'US',
                                            'zipcode' => '94105',
                                            'vatNumber' => null,
                                            'contact' => '+1 415 555 0100',
                                            'bankDetails' => null,
                                        ],
                                        'gdprEnabled' => false,
                                        'gdprAgreementEnabled' => false,
                                        'gdprAgreementLabel' => 'I agree with the terms and conditions.',
                                        'gdprAgreementContent' => null,
                                        'cookieConsentEnabled' => false,
                                        'cookieConsentPosition' => 'bottom-left',
                                        'cookieConsentDescription' => 'This website uses cookies to ensure you get the best experience on our website.',
                                        'cookieConsentStaticBlockIdentifier' => null,
                                        'cookieConsentCategories' => [
                                            'strictly_necessary' => 'I agree with the terms and conditions.',
                                            'basic_interaction' => 'These trackers enable basic interactions and functionalities.',
                                            'experience_enhancement' => 'These trackers help us to provide a personalized user experience.',
                                            'measurements' => 'These trackers help us to measure traffic and analyze your behavior.',
                                            'targeting_advertising' => 'These trackers help us to deliver personalized marketing content.',
                                        ],
                                        'euWithdrawalEnabled' => false,
                                        'returnWindowDays' => 7,
                                        'returnPolicy' => '7 days return policy.',
                                        'returnAllowedFileTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                                        'returnAllowedProductTypes' => ['simple', 'configurable', 'bundle', 'grouped'],
                                        'returnAfterCancelledAllowed' => true,
                                        'returnAfterDeclinedAllowed' => true,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: StoreConfigQueryResolver::class,
            args: [],
        ),
    ],
)]
class StoreConfig
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $id = null;

    public ?string $channel = null;

    public ?string $locale = null;

    public ?bool $guest_checkout_enabled = null;

    public ?bool $cart_cross_sell_enabled = null;

    public ?bool $estimate_shipping_enabled = null;

    public ?bool $reorder_enabled = null;

    public ?bool $admin_reorder_enabled = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['display_item_quantity', 'display_number_of_items_in_cart']])]
    public ?string $cart_summary = null;

    public ?bool $cart_page_enabled = null;

    public ?bool $mini_cart_enabled = null;

    public ?string $mini_cart_offer_info = null;

    public ?bool $backorders_enabled = null;

    public ?bool $minimum_order_enabled = null;

    public ?float $minimum_order_amount = null;

    public ?bool $minimum_order_includes_discount = null;

    public ?bool $minimum_order_includes_tax = null;

    public ?string $minimum_order_message = null;

    public ?string $order_number_prefix = null;

    public ?int $order_number_length = null;

    public ?string $order_number_suffix = null;

    public ?string $order_number_generator = null;

    public ?int $order_max_retry_attempts = null;

    public ?bool $customer_review_enabled = null;

    public ?bool $guest_review_enabled = null;

    public ?bool $reviewer_name_censored = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['star_counts', 'review_counts']])]
    public ?string $review_summary = null;

    public ?bool $compare_enabled = null;

    public ?bool $wishlist_enabled = null;

    public ?bool $image_search_enabled = null;

    public ?bool $social_share_enabled = null;

    /**
     * @var array<int, int>
     */
    #[ApiProperty(schema: ['type' => 'array', 'items' => ['type' => 'integer']])]
    public ?array $products_per_page = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['grid', 'list']])]
    public ?string $listing_mode = null;

    public ?string $default_sort_by = null;

    public ?bool $buy_now_button_enabled = null;

    public ?int $related_products_limit = null;

    public ?int $up_sell_products_limit = null;

    public ?int $cross_sell_products_limit = null;

    public ?int $search_min_query_length = null;

    public ?int $search_max_query_length = null;

    /**
     * @var array<int, string>
     */
    #[ApiProperty(schema: ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['facebook', 'twitter', 'pinterest', 'whatsapp', 'linkedin', 'email']]])]
    public ?array $social_share_providers = null;

    public ?string $social_share_message = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['default', 'sidebar']])]
    public ?string $category_view = null;

    public ?bool $breadcrumbs_enabled = null;

    public ?string $header_offer_title = null;

    public ?string $header_offer_link_title = null;

    public ?string $header_offer_link = null;

    public ?string $footer_copyright = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['kgs', 'lbs']])]
    public ?string $weight_unit = null;

    public ?string $custom_css = null;

    public ?string $custom_javascript = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ApiProperty(schema: ['type' => 'object', 'nullable' => true, 'additionalProperties' => true])]
    public ?array $speculation_rules = null;

    public ?bool $sitemap_enabled = null;

    public ?string $contact_name = null;

    public ?string $contact_email = null;

    /**
     * @var array<string, array<string, bool>>
     */
    #[ApiProperty(schema: ['type' => 'object', 'additionalProperties' => ['type' => 'object', 'additionalProperties' => ['type' => 'boolean']]])]
    public ?array $rich_snippets = null;

    public ?bool $ai_image_search_enabled = null;

    public ?bool $ai_review_translation_enabled = null;

    public ?bool $ai_checkout_message_enabled = null;

    public ?bool $email_verification_required = null;

    public ?bool $newsletter_enabled = null;

    public ?bool $captcha_enabled = null;

    public ?string $captcha_site_key = null;

    public ?bool $signup_newsletter_enabled = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['home', 'account']])]
    public ?string $login_redirect_to = null;

    public ?bool $address_country_required = null;

    public ?bool $address_state_required = null;

    public ?bool $address_postcode_required = null;

    public ?int $address_street_lines = null;

    /**
     * @var array<int, string>
     */
    #[ApiProperty(schema: ['type' => 'array', 'items' => ['type' => 'string', 'enum' => ['google', 'facebook', 'linkedin']]])]
    public ?array $social_login_providers = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['excluding_tax', 'including_tax', 'both']])]
    public ?string $tax_cart_display_prices = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['excluding_tax', 'including_tax', 'both']])]
    public ?string $tax_cart_display_subtotal = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['excluding_tax', 'including_tax', 'both']])]
    public ?string $tax_cart_display_shipping = null;

    public ?bool $tax_cart_show_breakdown = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['excluding_tax', 'including_tax', 'both']])]
    public ?string $tax_order_display_prices = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['excluding_tax', 'including_tax', 'both']])]
    public ?string $tax_order_display_subtotal = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['excluding_tax', 'including_tax', 'both']])]
    public ?string $tax_order_display_shipping = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['shipping_address', 'billing_address', 'shipping_origin']])]
    public ?string $tax_calculation_based_on = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['excluding_tax', 'including_tax']])]
    public ?string $tax_product_prices = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['excluding_tax', 'including_tax']])]
    public ?string $tax_shipping_prices = null;

    #[ApiProperty(description: 'Tax category applied to shipping (Sales → Taxes → Tax Categories). Null when set to None.')]
    public ?int $tax_shipping_category_id = null;

    #[ApiProperty(description: 'Default tax category for products that have none of their own. Null when set to None.')]
    public ?int $tax_product_category_id = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['before_discount', 'after_discount']])]
    public ?string $tax_apply_on = null;

    public ?string $tax_default_country = null;

    public ?string $tax_default_state = null;

    public ?string $tax_default_postcode = null;

    /**
     * @var array<string, string|null>
     */
    #[ApiProperty(schema: ['type' => 'object', 'additionalProperties' => ['type' => 'string', 'nullable' => true]])]
    public ?array $store_information = null;

    public ?bool $gdpr_enabled = null;

    public ?bool $gdpr_agreement_enabled = null;

    public ?string $gdpr_agreement_label = null;

    public ?string $gdpr_agreement_content = null;

    public ?bool $cookie_consent_enabled = null;

    #[ApiProperty(schema: ['type' => 'string', 'enum' => ['bottom-left', 'bottom-right', 'top-left', 'top-right', 'center']])]
    public ?string $cookie_consent_position = null;

    public ?string $cookie_consent_description = null;

    public ?string $cookie_consent_static_block_identifier = null;

    /**
     * @var array<string, string|null>
     */
    #[ApiProperty(schema: ['type' => 'object', 'additionalProperties' => ['type' => 'string', 'nullable' => true]])]
    public ?array $cookie_consent_categories = null;

    public ?bool $eu_withdrawal_enabled = null;

    public ?int $return_window_days = null;

    public ?string $return_policy = null;

    /**
     * @var array<int, string>
     */
    #[ApiProperty(schema: ['type' => 'array', 'items' => ['type' => 'string']])]
    public ?array $return_allowed_file_types = null;

    /**
     * @var array<int, string>
     */
    #[ApiProperty(schema: ['type' => 'array', 'items' => ['type' => 'string']])]
    public ?array $return_allowed_product_types = null;

    public ?bool $return_after_cancelled_allowed = null;

    public ?bool $return_after_declined_allowed = null;
}
