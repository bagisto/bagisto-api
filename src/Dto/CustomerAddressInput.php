<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Input DTO for customer address operations with token-based authentication
 * Token is passed via Authorization: Bearer header, NOT as input parameter
 *
 * NOTE: Token is NOT a DTO property. It is extracted from the Authorization header
 * via TokenHeaderFacade::getAuthorizationBearerToken() in the processor.
 */
class CustomerAddressInput
{
    /**
     * Identifier for API Platform GraphQL serialization
     */
    #[SerializedName('id')]
    #[ApiProperty(identifier: true)]
    #[Groups(['mutation'])]
    public ?int $id = null;

    /**
     * Address ID (required for update/delete, not used for create)
     */
    #[SerializedName('addressId')]
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $addressId = null;

    /**
     * First name
     */
    #[SerializedName('firstName')]
    #[Groups(['mutation'])]
    public ?string $firstName = null;

    /**
     * Last name
     */
    #[SerializedName('lastName')]
    #[Groups(['mutation'])]
    public ?string $lastName = null;

    /**
     * Company name
     */
    #[SerializedName('companyName')]
    #[Groups(['mutation'])]
    public ?string $companyName = null;

    /**
     * VAT ID
     */
    #[SerializedName('vatId')]
    #[Groups(['mutation'])]
    public ?string $vatId = null;

    /**
     * Email address
     */
    #[SerializedName('email')]
    #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * Phone number
     */
    #[SerializedName('phone')]
    #[Groups(['mutation'])]
    public ?string $phone = null;

    /**
     * Street address line 1
     */
    #[SerializedName('address1')]
    #[Groups(['mutation'])]
    public ?string $address1 = null;

    /**
     * Street address line 2
     */
    #[SerializedName('address2')]
    #[Groups(['mutation'])]
    public ?string $address2 = null;

    /**
     * Country
     */
    #[SerializedName('country')]
    #[Groups(['mutation'])]
    public ?string $country = null;

    /**
     * State/Province
     */
    #[SerializedName('state')]
    #[Groups(['mutation'])]
    public ?string $state = null;

    /**
     * City
     */
    #[SerializedName('city')]
    #[Groups(['mutation'])]
    public ?string $city = null;

    /**
     * Postal code
     */
    #[SerializedName('postcode')]
    #[Groups(['mutation'])]
    public ?string $postcode = null;

    /**
     * Set as default address
     */
    #[SerializedName('defaultAddress')]
    #[Groups(['mutation'])]
    public ?bool $defaultAddress = null;
}
