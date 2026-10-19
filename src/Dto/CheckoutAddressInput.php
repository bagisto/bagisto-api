<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class CheckoutAddressInput
{
    // Billing Address
    /**
     * The billing first name.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing first name')]
    #[SerializedName('billingFirstName')]
    public ?string $billingFirstName = null;

    /**
     * The billing last name.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing last name')]
    #[SerializedName('billingLastName')]
    public ?string $billingLastName = null;

    /**
     * The billing email.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing email')]
    #[SerializedName('billingEmail')]
    public ?string $billingEmail = null;

    /**
     * The billing company name.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing company name')]
    #[SerializedName('billingCompanyName')]
    public ?string $billingCompanyName = null;

    /**
     * The billing address.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing address')]
    #[SerializedName('billingAddress')]
    public ?string $billingAddress = null;

    /**
     * The billing country.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing country')]
    #[SerializedName('billingCountry')]
    public ?string $billingCountry = null;

    /**
     * The billing state.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing state')]
    #[SerializedName('billingState')]
    public ?string $billingState = null;

    /**
     * The billing city.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing city')]
    #[SerializedName('billingCity')]
    public ?string $billingCity = null;

    /**
     * The billing postcode.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing postcode')]
    #[SerializedName('billingPostcode')]
    public ?string $billingPostcode = null;

    /**
     * The billing phone number.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Billing phone number')]
    #[SerializedName('billingPhoneNumber')]
    public ?string $billingPhoneNumber = null;

    // Shipping Address
    /**
     * The shipping first name.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping first name')]
    #[SerializedName('shippingFirstName')]
    public ?string $shippingFirstName = null;

    /**
     * The shipping last name.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping last name')]
    #[SerializedName('shippingLastName')]
    public ?string $shippingLastName = null;

    /**
     * The shipping email.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping email')]
    #[SerializedName('shippingEmail')]
    public ?string $shippingEmail = null;

    /**
     * The shipping company name.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping company name')]
    #[SerializedName('shippingCompanyName')]
    public ?string $shippingCompanyName = null;

    /**
     * The shipping address.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping address')]
    #[SerializedName('shippingAddress')]
    public ?string $shippingAddress = null;

    /**
     * The shipping country.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping country')]
    #[SerializedName('shippingCountry')]
    public ?string $shippingCountry = null;

    /**
     * The shipping state.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping state')]
    #[SerializedName('shippingState')]
    public ?string $shippingState = null;

    /**
     * The shipping city.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping city')]
    #[SerializedName('shippingCity')]
    public ?string $shippingCity = null;

    /**
     * The shipping postcode.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping postcode')]
    #[SerializedName('shippingPostcode')]
    public ?string $shippingPostcode = null;

    /**
     * The shipping phone number.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping phone number')]
    #[SerializedName('shippingPhoneNumber')]
    public ?string $shippingPhoneNumber = null;

    // Flags
    /**
     * The use for shipping.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Use address for shipping')]
    #[SerializedName('useForShipping')]
    public ?bool $useForShipping = null;

    // Additional fields for shipping and payment methods
    /**
     * The shipping method.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Shipping method code')]
    #[SerializedName('shippingMethod')]
    public ?string $shippingMethod = null;

    /**
     * The payment method.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment method code')]
    #[SerializedName('paymentMethod')]
    public ?string $paymentMethod = null;

    // Payment callback URLs (for headless frontends)
    /**
     * The payment success url.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment success callback URL')]
    #[SerializedName('paymentSuccessUrl')]
    public ?string $paymentSuccessUrl = null;

    /**
     * The payment failure url.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment failure callback URL')]
    #[SerializedName('paymentFailureUrl')]
    public ?string $paymentFailureUrl = null;

    /**
     * The payment cancel url.
     */
    #[Groups(['mutation'])]
    #[ApiProperty(description: 'Payment cancel callback URL')]
    #[SerializedName('paymentCancelUrl')]
    public ?string $paymentCancelUrl = null;
}
