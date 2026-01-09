<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * CheckoutAddressOutput - GraphQL Output DTO for Checkout Address
 *
 * Output for retrieving billing and shipping addresses during checkout
 */
class CheckoutAddressOutput
{
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $cartToken = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $customerId = null;

    // Billing Address
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingFirstName = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingLastName = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingEmail = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingCompanyName = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingAddress = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingCountry = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingState = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingCity = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingPostcode = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $billingPhoneNumber = null;

    // Shipping Address
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingFirstName = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingLastName = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingEmail = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingCompanyName = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingAddress = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingCountry = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingState = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingCity = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingPostcode = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $shippingPhoneNumber = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?bool $success = null;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;
}
