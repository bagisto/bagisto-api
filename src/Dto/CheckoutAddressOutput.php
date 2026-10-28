<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CheckoutAddressOutput
{
    /**
     * The id.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    /**
     * The cart token.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $cartToken = null;

    /**
     * The customer id.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $customerId = null;

    // Billing Address
    /**
     * The billing first name.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingFirstName = null;

    /**
     * The billing last name.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingLastName = null;

    /**
     * The billing email.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingEmail = null;

    /**
     * The billing company name.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingCompanyName = null;

    /**
     * The billing address.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingAddress = null;

    /**
     * The billing country.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingCountry = null;

    /**
     * The billing state.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingState = null;

    /**
     * The billing city.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingCity = null;

    /**
     * The billing postcode.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingPostcode = null;

    /**
     * The billing phone number.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingPhoneNumber = null;

    // Shipping Address
    /**
     * The shipping first name.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingFirstName = null;

    /**
     * The shipping last name.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingLastName = null;

    /**
     * The shipping email.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingEmail = null;

    /**
     * The shipping company name.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingCompanyName = null;

    /**
     * The shipping address.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingAddress = null;

    /**
     * The shipping country.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingCountry = null;

    /**
     * The shipping state.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingState = null;

    /**
     * The shipping city.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingCity = null;

    /**
     * The shipping postcode.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingPostcode = null;

    /**
     * The shipping phone number.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingPhoneNumber = null;

    /**
     * The success.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;
}
