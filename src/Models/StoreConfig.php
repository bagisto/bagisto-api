<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\StoreConfigQueryResolver;
use Webkul\BagistoApi\State\StoreConfigProvider;

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
                summary: 'Get the public store settings of the current channel',
                description: 'Returns the settings a storefront or app needs before the customer logs in: guest checkout, minimum order amount, review rules, compare and wishlist switches, social login providers, tax display and optional features. Only non-secret settings are returned. Send `X-Channel` and `X-Locale` to read another channel or locale. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The store settings of the current channel.',
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
                                        'minimumOrderEnabled' => true,
                                        'minimumOrderAmount' => 25.0,
                                        'minimumOrderIncludesDiscount' => false,
                                        'minimumOrderIncludesTax' => false,
                                        'minimumOrderMessage' => 'The minimum order amount is 25.',
                                        'customerReviewEnabled' => true,
                                        'guestReviewEnabled' => false,
                                        'reviewerNameCensored' => true,
                                        'reviewSummary' => 'review_counts',
                                        'compareEnabled' => true,
                                        'wishlistEnabled' => true,
                                        'buyNowEnabled' => true,
                                        'imageSearchEnabled' => true,
                                        'socialShareEnabled' => false,
                                        'productsPerPage' => 12,
                                        'listingMode' => 'grid',
                                        'defaultSortBy' => 'name-desc',
                                        'emailVerificationRequired' => false,
                                        'newsletterEnabled' => true,
                                        'socialLoginProviders' => ['google', 'facebook'],
                                        'captchaEnabled' => false,
                                        'taxCartDisplayPrices' => 'excluding_tax',
                                        'taxCartDisplaySubtotal' => 'excluding_tax',
                                        'taxCartDisplayShipping' => 'excluding_tax',
                                        'taxCartShowBreakdown' => false,
                                        'taxOrderDisplayPrices' => 'excluding_tax',
                                        'taxOrderDisplaySubtotal' => 'excluding_tax',
                                        'taxOrderDisplayShipping' => 'excluding_tax',
                                        'gdprEnabled' => false,
                                        'euWithdrawalEnabled' => false,
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
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?string $id = null;

    /**
     * The channel.
     */
    public ?string $channel = null;

    /**
     * The locale.
     */
    public ?string $locale = null;

    /**
     * The guest checkout enabled.
     */
    public ?bool $guest_checkout_enabled = null;

    /**
     * The cart cross sell enabled.
     */
    public ?bool $cart_cross_sell_enabled = null;

    /**
     * The estimate shipping enabled.
     */
    public ?bool $estimate_shipping_enabled = null;

    /**
     * The reorder enabled.
     */
    public ?bool $reorder_enabled = null;

    /**
     * The minimum order enabled.
     */
    public ?bool $minimum_order_enabled = null;

    /**
     * The minimum order amount.
     */
    public ?float $minimum_order_amount = null;

    /**
     * The minimum order includes discount.
     */
    public ?bool $minimum_order_includes_discount = null;

    /**
     * The minimum order includes tax.
     */
    public ?bool $minimum_order_includes_tax = null;

    /**
     * The minimum order message.
     */
    public ?string $minimum_order_message = null;

    /**
     * The customer review enabled.
     */
    public ?bool $customer_review_enabled = null;

    /**
     * The guest review enabled.
     */
    public ?bool $guest_review_enabled = null;

    /**
     * The reviewer name censored.
     */
    public ?bool $reviewer_name_censored = null;

    /**
     * The review summary.
     */
    public ?string $review_summary = null;

    /**
     * The compare enabled.
     */
    public ?bool $compare_enabled = null;

    /**
     * The wishlist enabled.
     */
    public ?bool $wishlist_enabled = null;

    /**
     * The buy now enabled.
     */
    public ?bool $buy_now_enabled = null;

    /**
     * The image search enabled.
     */
    public ?bool $image_search_enabled = null;

    /**
     * The social share enabled.
     */
    public ?bool $social_share_enabled = null;

    /**
     * The products per page.
     */
    public ?int $products_per_page = null;

    /**
     * The listing mode.
     */
    public ?string $listing_mode = null;

    /**
     * The default sort by.
     */
    public ?string $default_sort_by = null;

    /**
     * The email verification required.
     */
    public ?bool $email_verification_required = null;

    /**
     * The newsletter enabled.
     */
    public ?bool $newsletter_enabled = null;

    /**
     * The social login providers enabled for the storefront.
     *
     * @var array<int, string>
     */
    #[ApiProperty(
        writable: false,
        schema: [
            'type' => 'array',
            'items' => ['type' => 'string'],
        ],
    )]
    public ?array $social_login_providers = null;

    /**
     * The captcha enabled.
     */
    public ?bool $captcha_enabled = null;

    /**
     * The tax cart display prices.
     */
    public ?string $tax_cart_display_prices = null;

    /**
     * The tax cart display subtotal.
     */
    public ?string $tax_cart_display_subtotal = null;

    /**
     * The tax cart display shipping.
     */
    public ?string $tax_cart_display_shipping = null;

    /**
     * The tax cart show breakdown.
     */
    public ?bool $tax_cart_show_breakdown = null;

    /**
     * The tax order display prices.
     */
    public ?string $tax_order_display_prices = null;

    /**
     * The tax order display subtotal.
     */
    public ?string $tax_order_display_subtotal = null;

    /**
     * The tax order display shipping.
     */
    public ?string $tax_order_display_shipping = null;

    /**
     * The gdpr enabled.
     */
    public ?bool $gdpr_enabled = null;

    /**
     * The eu withdrawal enabled.
     */
    public ?bool $eu_withdrawal_enabled = null;
}
