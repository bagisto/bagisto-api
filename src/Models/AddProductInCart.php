<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;

/**
 * AddProductInCart - GraphQL & REST API Resource for Adding Products to Cart
 *
 * Provides mutation for adding products to an existing shopping cart.
 * Uses token-based authentication for guest users or bearer token for authenticated users.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'AddProductInCart',
    uriTemplate: '/add-product-in-cart',
    operations: [
        new Post(
            name: 'addProduct',
            uriTemplate: '/add-product-in-cart',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            deserialize: false,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Add product to cart. Can be used for both authenticated users and guests.',
            openapi: new Model\Operation(
                tags: ['Cart'],
                summary: 'Add product to cart',
                description: 'Add a product to the shopping cart with quantity and optional product options.',
                requestBody: new Model\RequestBody(
                    description: 'Product to add to cart',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'productId'                  => ['type' => 'integer', 'example' => 1, 'description' => 'Product ID'],
                                    'quantity'                   => ['type' => 'integer', 'example' => 1, 'description' => 'Quantity'],
                                    'selectedConfigurableOption' => ['type' => 'integer', 'description' => 'Child variant product ID (configurable products)'],
                                    'superAttribute'             => ['type' => 'object', 'description' => 'Super attribute values {attributeId: optionValue} (configurable products)'],
                                    'qty'                        => ['type' => 'object', 'description' => 'Quantities per associated product {productId: quantity} (grouped products)'],
                                    'bundleOptions'              => ['type' => 'object', 'description' => 'Bundle options {optionId: [productIds]} (bundle products)'],
                                    'bundleOptionQty'            => ['type' => 'object', 'description' => 'Bundle option quantities {optionId: quantity} (bundle products, optional)'],
                                    'links'                      => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Download link IDs (downloadable products)'],
                                    'booking'                    => ['type' => 'object', 'description' => 'Booking data - varies by booking type (appointment, default, table, rental, event)'],
                                    'specialNote'                => ['type' => 'string', 'description' => 'Special note for table bookings'],
                                    'isBuyNow'                   => ['type' => 'integer', 'enum' => [0, 1], 'description' => 'Buy now flag (0 = add to cart, 1 = buy now)'],
                                ],
                            ],
                            'examples' => [
                                'simple_product' => [
                                    'summary'     => 'Add Simple Product',
                                    'description' => 'Add a simple product to cart',
                                    'value'       => [
                                        'productId' => 1,
                                        'quantity'  => 1,
                                    ],
                                ],
                                'virtual_product' => [
                                    'summary'     => 'Add Virtual Product',
                                    'description' => 'Add a virtual product (no shipping required)',
                                    'value'       => [
                                        'productId' => 61,
                                        'quantity'  => 1,
                                    ],
                                ],
                                'configurable_product' => [
                                    'summary'     => 'Add Configurable Product',
                                    'description' => 'Add a configurable product by selecting variant options',
                                    'value'       => [
                                        'productId'                  => 7,
                                        'quantity'                   => 1,
                                        'selectedConfigurableOption' => 8,
                                        'superAttribute'             => ['23' => 3, '24' => 7],
                                    ],
                                ],
                                'grouped_product' => [
                                    'summary'     => 'Add Grouped Product',
                                    'description' => 'Add a grouped product by specifying quantities for each associated product',
                                    'value'       => [
                                        'productId' => 5,
                                        'quantity'  => 1,
                                        'qty'       => ['1' => 2, '3' => 1, '4' => 1],
                                    ],
                                ],
                                'bundle_product' => [
                                    'summary'     => 'Add Bundle Product',
                                    'description' => 'Add a bundle product with selected bundle options',
                                    'value'       => [
                                        'productId'     => 6,
                                        'quantity'      => 1,
                                        'bundleOptions' => ['1' => [1], '2' => [2], '3' => [3], '4' => [4]],
                                    ],
                                ],
                                'downloadable_product' => [
                                    'summary'     => 'Add Downloadable Product',
                                    'description' => 'Add a downloadable product with selected download links',
                                    'value'       => [
                                        'productId' => 62,
                                        'quantity'  => 1,
                                        'links'     => [1, 2],
                                    ],
                                ],
                                'appointment_booking' => [
                                    'summary'     => 'Add Appointment Booking',
                                    'description' => 'Book an appointment by selecting date and time slot',
                                    'value'       => [
                                        'productId' => 63,
                                        'quantity'  => 1,
                                        'booking'   => [
                                            'date' => '2026-04-24',
                                            'slot' => '09:00 AM - 10:00 AM',
                                        ],
                                    ],
                                ],
                                'default_booking' => [
                                    'summary'     => 'Add Default Booking',
                                    'description' => 'Book a default booking slot by selecting date and time',
                                    'value'       => [
                                        'productId' => 64,
                                        'quantity'  => 1,
                                        'booking'   => [
                                            'date' => '2026-04-24',
                                            'slot' => '12:00 PM - 01:00 PM',
                                        ],
                                    ],
                                ],
                                'table_booking' => [
                                    'summary'     => 'Add Table Booking',
                                    'description' => 'Reserve a restaurant table with date, time slot and special note',
                                    'value'       => [
                                        'productId' => 65,
                                        'quantity'  => 1,
                                        'booking'   => [
                                            'date' => '2026-04-24',
                                            'slot' => '09:00 AM - 10:30 AM',
                                            'note' => 'Window seat please',
                                        ],
                                    ],
                                ],
                                'rental_daily_booking' => [
                                    'summary'     => 'Add Rental Booking (Daily)',
                                    'description' => 'Rent a product for a date range',
                                    'value'       => [
                                        'productId' => 66,
                                        'quantity'  => 1,
                                        'booking'   => [
                                            'renting_type' => 'daily',
                                            'date_from'    => '2026-04-24',
                                            'date_to'      => '2026-04-26',
                                        ],
                                    ],
                                ],
                                'event_booking' => [
                                    'summary'     => 'Add Event Booking',
                                    'description' => 'Book event tickets by specifying quantities per ticket type',
                                    'value'       => [
                                        'productId' => 67,
                                        'quantity'  => 1,
                                        'booking'   => [
                                            'qty' => ['37' => 2, '38' => 1],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Add product to cart. Can be used for both authenticated users and guests.',
        ),
    ]
)]
class AddProductInCart
{
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $cartToken = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?int $customerId = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?int $channelId = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?int $itemsCount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?array $items = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $subtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseSubtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $discountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseDiscountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $taxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseTaxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $shippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseShippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $grandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseGrandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedSubtotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedDiscountAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedTaxAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedShippingAmount = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedGrandTotal = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $couponCode = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?array $carts = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $sessionToken = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isGuest = null;
}
