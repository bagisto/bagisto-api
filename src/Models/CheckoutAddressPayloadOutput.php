<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;

class CheckoutAddressPayloadOutput
{
    /**
     * The success.
     */
    #[ApiProperty(writable: false, readable: true)]
    public bool $success = false;

    /**
     * The message.
     */
    #[ApiProperty(writable: false, readable: true)]
    public string $message = '';

    /**
     * The id.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?int $id = null;

    /**
     * The cart token.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $cartToken = null;

    /**
     * The customer id.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?int $customerId = null;

    // Billing Address
    /**
     * The billing first name.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingFirstName = null;

    /**
     * The billing last name.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingLastName = null;

    /**
     * The billing email.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingEmail = null;

    /**
     * The billing company name.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingCompanyName = null;

    /**
     * The billing address.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingAddress = null;

    /**
     * The billing country.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingCountry = null;

    /**
     * The billing state.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingState = null;

    /**
     * The billing city.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingCity = null;

    /**
     * The billing postcode.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingPostcode = null;

    /**
     * The billing phone number.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $billingPhoneNumber = null;

    // Shipping Address
    /**
     * The shipping first name.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingFirstName = null;

    /**
     * The shipping last name.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingLastName = null;

    /**
     * The shipping email.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingEmail = null;

    /**
     * The shipping company name.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingCompanyName = null;

    /**
     * The shipping address.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingAddress = null;

    /**
     * The shipping country.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingCountry = null;

    /**
     * The shipping state.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingState = null;

    /**
     * The shipping city.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingCity = null;

    /**
     * The shipping postcode.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingPostcode = null;

    /**
     * The shipping phone number.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $shippingPhoneNumber = null;
}
