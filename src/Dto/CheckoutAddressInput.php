<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * CheckoutAddressInput - GraphQL Input DTO for Checkout Address
 *
 * Input for storing billing and shipping addresses during checkout
 * Authentication token is passed via Authorization: Bearer header, NOT as input parameter
 */
class CheckoutAddressInput
{
    // Billing Address
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing first name')]
    public ?string $billingFirstName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing last name')]
    public ?string $billingLastName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing email')]
    public ?string $billingEmail = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing company name')]
    public ?string $billingCompanyName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing address')]
    public ?string $billingAddress = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing country')]
    public ?string $billingCountry = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing state')]
    public ?string $billingState = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing city')]
    public ?string $billingCity = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing postcode')]
    public ?string $billingPostcode = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing phone number')]
    public ?string $billingPhoneNumber = null;

    // Shipping Address
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping first name')]
    public ?string $shippingFirstName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping last name')]
    public ?string $shippingLastName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping email')]
    public ?string $shippingEmail = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping company name')]
    public ?string $shippingCompanyName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping address')]
    public ?string $shippingAddress = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping country')]
    public ?string $shippingCountry = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping state')]
    public ?string $shippingState = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping city')]
    public ?string $shippingCity = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping postcode')]
    public ?string $shippingPostcode = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping phone number')]
    public ?string $shippingPhoneNumber = null;

    // Flags
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Use address for shipping')]
    public ?bool $useForShipping = null;

    // Additional fields for shipping and payment methods
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping method code')]
    public ?string $shippingMethod = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment method code')]
    public ?string $paymentMethod = null;

    // Payment callback URLs (for headless frontends)
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment success callback URL')]
    public ?string $paymentSuccessUrl = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment failure callback URL')]
    public ?string $paymentFailureUrl = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment cancel callback URL')]
    public ?string $paymentCancelUrl = null;
}
