<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\State\CartTokenMutationProvider;
use Webkul\BagistoApi\State\CartTokenProcessor;

/**
 * GetCartToken - GraphQL API Resource for Reading Cart Details
 *
 * Provides mutation for reading cart details by cart ID.
 * Uses separate resource to avoid ID requirement in mutations.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/get-cart-tokens',
    shortName: 'GetCartToken',
    operations: [
        new Get(uriTemplate: '/get-cart-tokens/{id}'),
        new GetCollection(uriTemplate: '/get-cart-tokens'),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'read',
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
            description: 'Get cart details by cart ID',
        ),
    ]
)]
class GetCartToken
{
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
    public ?string $sessionToken = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $isGuest = null;
}
