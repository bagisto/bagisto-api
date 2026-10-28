<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CustomerAddressTokenOutput
{
    /**
     * Address id, also the resource identifier.
     */
    #[ApiProperty(identifier: true)]
    #[Groups(['query'])]
    public ?int $id = null;

    /**
     * Address id.
     */
    #[Groups(['query'])]
    public ?int $address_id = null;

    /**
     * First name.
     */
    #[Groups(['query'])]
    public ?string $first_name = null;

    /**
     * Last name.
     */
    #[Groups(['query'])]
    public ?string $last_name = null;

    /**
     * Company name.
     */
    #[Groups(['query'])]
    public ?string $company_name = null;

    /**
     * VAT id.
     */
    #[Groups(['query'])]
    public ?string $vat_id = null;

    /**
     * Email.
     */
    #[Groups(['query'])]
    public ?string $email = null;

    /**
     * Phone.
     */
    #[Groups(['query'])]
    public ?string $phone = null;

    /**
     * First street line.
     */
    #[Groups(['query'])]
    public ?string $address1 = null;

    /**
     * Second street line.
     */
    #[Groups(['query'])]
    public ?string $address2 = null;

    /**
     * Country code.
     */
    #[Groups(['query'])]
    public ?string $country = null;

    /**
     * State code.
     */
    #[Groups(['query'])]
    public ?string $state = null;

    /**
     * City.
     */
    #[Groups(['query'])]
    public ?string $city = null;

    /**
     * Postcode.
     */
    #[Groups(['query'])]
    public ?string $postcode = null;

    /**
     * Whether this is the customer's default address.
     */
    #[Groups(['query'])]
    public ?bool $default_address = null;
}
