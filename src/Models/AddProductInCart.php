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
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups'                 => ['mutation'],
            ],
            description: 'Add product to cart. Can be used for both authenticated users and guests.',
            openapi: new Model\Operation(
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
                                    'productId' => [
                                        'type'        => 'integer',
                                        'example'     => 1,
                                        'description' => 'Product ID',
                                    ],
                                    'quantity' => [
                                        'type'        => 'integer',
                                        'example'     => 1,
                                        'description' => 'Quantity',
                                    ],
                                    'options' => [
                                        'type'        => 'object',
                                        'example'     => ['size' => 'M', 'color' => 'blue'],
                                        'description' => 'Product options (optional)',
                                    ],
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
                                'product_with_options' => [
                                    'summary'     => 'Add Product with Options',
                                    'description' => 'Add a product with size and color options',
                                    'value'       => [
                                        'productId' => 2,
                                        'quantity'  => 2,
                                        'options'   => ['size' => 'M', 'color' => 'blue'],
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
