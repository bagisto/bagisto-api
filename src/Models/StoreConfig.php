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
                description: 'Returns the store settings a storefront needs before it renders a page: checkout and minimum order rules, review rules, product listing options, customer account options, tax display and optional features. Only non-secret values are returned. Send `X-Channel` or `X-Locale` to read another channel or language. Public endpoint — the storefront key is enough.',
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
                                        'minimumOrderEnabled' => true,
                                        'minimumOrderAmount' => 50.0,
                                        'minimumOrderIncludesDiscount' => false,
                                        'minimumOrderIncludesTax' => false,
                                        'minimumOrderMessage' => 'Orders must be at least $50.',
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
                                        'emailVerificationRequired' => false,
                                        'newsletterEnabled' => true,
                                        'captchaEnabled' => false,
                                        'socialLoginProviders' => ['google', 'facebook'],
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
    #[ApiProperty(identifier: true, writable: false)]
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

    public ?bool $email_verification_required = null;

    public ?bool $newsletter_enabled = null;

    public ?bool $captcha_enabled = null;

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

    public ?bool $gdpr_enabled = null;

    public ?bool $eu_withdrawal_enabled = null;
}
