<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/get-cart-tokens',
    shortName: 'GetCartToken',
    operations: [],
    graphQlOperations: [
        new Mutation(
            name: 'read',
            input: CartInput::class,
            output: CartData::class,
            provider: CartTokenMutationProvider::class,
            processor: CartTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Get the cart identified by the Bearer token (customer token or guest cart token).',
        ),
    ]
)]
class GetCartToken
{
    /**
     * The cart token.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $cartToken = null;

    /**
     * The customer id.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?int $customerId = null;

    /**
     * The channel id.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?int $channelId = null;

    /**
     * The items count.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?int $itemsCount = null;

    /**
     * The items.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?array $items = null;

    /**
     * The subtotal.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $subtotal = null;

    /**
     * The base subtotal.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseSubtotal = null;

    /**
     * The discount amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $discountAmount = null;

    /**
     * The base discount amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseDiscountAmount = null;

    /**
     * The tax amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $taxAmount = null;

    /**
     * The base tax amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseTaxAmount = null;

    /**
     * The shipping amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $shippingAmount = null;

    /**
     * The base shipping amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseShippingAmount = null;

    /**
     * The grand total.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $grandTotal = null;

    /**
     * The base grand total.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $baseGrandTotal = null;

    /**
     * The formatted subtotal.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedSubtotal = null;

    /**
     * The formatted discount amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedDiscountAmount = null;

    /**
     * The formatted tax amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedTaxAmount = null;

    /**
     * The formatted shipping amount.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedShippingAmount = null;

    /**
     * The formatted grand total.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $formattedGrandTotal = null;

    /**
     * The coupon code.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $couponCode = null;

    /**
     * The success.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;

    /**
     * The session token.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $sessionToken = null;

    /**
     * The is guest.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isGuest = null;
}
