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

/**
 * The public store settings a storefront or mobile app needs, for the current channel.
 *
 * Only a fixed list of non-secret settings is exposed. Keys, secrets and credentials never are.
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
    #[ApiProperty(identifier: true)]
    public ?string $id = null;

    public ?string $channel = null;

    public ?string $locale = null;

    public ?bool $guest_checkout_enabled = null;

    public ?bool $cart_cross_sell_enabled = null;

    public ?bool $estimate_shipping_enabled = null;

    public ?bool $reorder_enabled = null;

    public ?bool $minimum_order_enabled = null;

    public ?float $minimum_order_amount = null;

    public ?bool $minimum_order_includes_discount = null;

    public ?bool $minimum_order_includes_tax = null;

    public ?string $minimum_order_message = null;

    public ?bool $customer_review_enabled = null;

    public ?bool $guest_review_enabled = null;

    public ?bool $reviewer_name_censored = null;

    public ?string $review_summary = null;

    public ?bool $compare_enabled = null;

    public ?bool $wishlist_enabled = null;

    public ?bool $image_search_enabled = null;

    public ?bool $social_share_enabled = null;

    public ?int $products_per_page = null;

    public ?string $listing_mode = null;

    public ?string $default_sort_by = null;

    public ?bool $email_verification_required = null;

    public ?bool $newsletter_enabled = null;

    /**
     * @var array<int, string>
     */
    #[ApiProperty(
        writable: false,
        schema: ['type' => 'array', 'items' => ['type' => 'string']],
    )]
    public ?array $social_login_providers = null;

    public ?bool $captcha_enabled = null;

    public ?string $tax_cart_display_prices = null;

    public ?string $tax_cart_display_subtotal = null;

    public ?string $tax_cart_display_shipping = null;

    public ?bool $tax_cart_show_breakdown = null;

    public ?string $tax_order_display_prices = null;

    public ?string $tax_order_display_subtotal = null;

    public ?string $tax_order_display_shipping = null;

    public ?bool $gdpr_enabled = null;

    public ?bool $eu_withdrawal_enabled = null;
}
