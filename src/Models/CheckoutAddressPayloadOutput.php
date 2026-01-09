<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;

/**
 * CheckoutAddressPayloadOutput - Response model for CreateCheckoutAddress mutation
 *
 * Defines the structure of the GraphQL response for the checkout address mutation
 */
class CheckoutAddressPayloadOutput
{
    #[ApiProperty(writable: false, readable: true)]
    public bool $success = false;

    #[ApiProperty(writable: false, readable: true)]
    public string $message = '';

    #[ApiProperty(writable: false, readable: true)]
    public ?int $id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $cartToken = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?int $customerId = null;

    // Billing Address
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingFirstName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingLastName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingEmail = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingCompanyName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingAddress = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingCountry = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingState = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingCity = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingPostcode = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingPhoneNumber = null;

    // Shipping Address
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingFirstName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingLastName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingEmail = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingCompanyName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingAddress = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingCountry = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingState = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingCity = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingPostcode = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingPhoneNumber = null;
}
