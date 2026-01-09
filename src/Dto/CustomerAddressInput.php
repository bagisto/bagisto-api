<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

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
     * Address ID (required for update/delete, not used for create)
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation'])]
    public ?int $addressId = null;

    /**
     * First name
     */
    #[Groups(['mutation'])]
    public ?string $firstName = null;

    /**
     * Last name
     */
    #[Groups(['mutation'])]
    public ?string $lastName = null;

    /**
     * Email address
     */
    #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * Phone number
     */
    #[Groups(['mutation'])]
    public ?string $phone = null;

    /**
     * Street address line 1
     */
    #[Groups(['mutation'])]
    public ?string $address1 = null;

    /**
     * Street address line 2
     */
    #[Groups(['mutation'])]
    public ?string $address2 = null;

    /**
     * Country
     */
    #[Groups(['mutation'])]
    public ?string $country = null;

    /**
     * State/Province
     */
    #[Groups(['mutation'])]
    public ?string $state = null;

    /**
     * City
     */
    #[Groups(['mutation'])]
    public ?string $city = null;

    /**
     * Postal code
     */
    #[Groups(['mutation'])]
    public ?string $postcode = null;

    /**
     * Use for shipping
     */
    #[Groups(['mutation'])]
    public ?bool $useForShipping = null;

    /**
     * Set as default address
     */
    #[Groups(['mutation'])]
    public ?bool $defaultAddress = null;
}
