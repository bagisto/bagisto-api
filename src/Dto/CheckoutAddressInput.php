<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

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
    #[SerializedName('billingFirstName')]
    public ?string $billingFirstName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing last name')]
    #[SerializedName('billingLastName')]
    public ?string $billingLastName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing email')]
    #[SerializedName('billingEmail')]
    public ?string $billingEmail = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing company name')]
    #[SerializedName('billingCompanyName')]
    public ?string $billingCompanyName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing VAT number')]
    #[SerializedName('billingVatId')]
    public ?string $billingVatId = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing address')]
    #[SerializedName('billingAddress')]
    public ?string $billingAddress = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing country')]
    #[SerializedName('billingCountry')]
    public ?string $billingCountry = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing state')]
    #[SerializedName('billingState')]
    public ?string $billingState = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing city')]
    #[SerializedName('billingCity')]
    public ?string $billingCity = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing postcode')]
    #[SerializedName('billingPostcode')]
    public ?string $billingPostcode = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing phone number')]
    #[SerializedName('billingPhoneNumber')]
    public ?string $billingPhoneNumber = null;

    // Shipping Address
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping first name')]
    #[SerializedName('shippingFirstName')]
    public ?string $shippingFirstName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping last name')]
    #[SerializedName('shippingLastName')]
    public ?string $shippingLastName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping email')]
    #[SerializedName('shippingEmail')]
    public ?string $shippingEmail = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping company name')]
    #[SerializedName('shippingCompanyName')]
    public ?string $shippingCompanyName = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping address')]
    #[SerializedName('shippingAddress')]
    public ?string $shippingAddress = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping country')]
    #[SerializedName('shippingCountry')]
    public ?string $shippingCountry = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping state')]
    #[SerializedName('shippingState')]
    public ?string $shippingState = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping city')]
    #[SerializedName('shippingCity')]
    public ?string $shippingCity = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping postcode')]
    #[SerializedName('shippingPostcode')]
    public ?string $shippingPostcode = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping phone number')]
    #[SerializedName('shippingPhoneNumber')]
    public ?string $shippingPhoneNumber = null;

    // Flags
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Use address for shipping')]
    #[SerializedName('useForShipping')]
    public ?bool $useForShipping = null;

    // Additional fields for shipping and payment methods
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping method code')]
    #[SerializedName('shippingMethod')]
    public ?string $shippingMethod = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment method code')]
    #[SerializedName('paymentMethod')]
    public ?string $paymentMethod = null;

    // Payment callback URLs (for headless frontends)
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment success callback URL')]
    #[SerializedName('paymentSuccessUrl')]
    public ?string $paymentSuccessUrl = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment failure callback URL')]
    #[SerializedName('paymentFailureUrl')]
    public ?string $paymentFailureUrl = null;

    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment cancel callback URL')]
    #[SerializedName('paymentCancelUrl')]
    public ?string $paymentCancelUrl = null;
}
