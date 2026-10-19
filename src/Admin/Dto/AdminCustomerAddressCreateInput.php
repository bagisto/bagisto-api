<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerAddressCreateInput
{
    /**
     * The customer id.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $customer_id = null;

    /**
     * The first name.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $first_name = null;

    /**
     * The last name.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $last_name = null;

    /**
     * The company name.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $company_name = null;

    /**
     * The vat id.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $vat_id = null;

    /**
     * The address.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $address = null;

    /**
     * The city.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $city = null;

    /**
     * The state.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $state = null;

    /**
     * The country.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $country = null;

    /**
     * The postcode.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $postcode = null;

    /**
     * The email.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * The phone.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $phone = null;

    /**
     * The default address.
     */
    #[ApiProperty] #[Groups(['mutation'])]
    public ?bool $default_address = null;
}
