<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class CustomerAddressOutput
{
    /**
     * The id.
     */
    #[Groups(['read'])]
    public ?int $id = null;

    /**
     * The first name.
     */
    #[Groups(['read'])]
    public ?string $firstName = null;

    /**
     * The last name.
     */
    #[Groups(['read'])]
    public ?string $lastName = null;

    /**
     * The email.
     */
    #[Groups(['read'])]
    public ?string $email = null;

    /**
     * The phone.
     */
    #[Groups(['read'])]
    public ?string $phone = null;

    /**
     * The company.
     */
    #[Groups(['read'])]
    public ?string $company = null;

    /**
     * The address1.
     */
    #[Groups(['read'])]
    public ?string $address1 = null;

    /**
     * The address2.
     */
    #[Groups(['read'])]
    public ?string $address2 = null;

    /**
     * The country.
     */
    #[Groups(['read'])]
    public ?string $country = null;

    /**
     * The state.
     */
    #[Groups(['read'])]
    public ?string $state = null;

    /**
     * The city.
     */
    #[Groups(['read'])]
    public ?string $city = null;

    /**
     * The postcode.
     */
    #[Groups(['read'])]
    public ?string $postcode = null;

    /**
     * The use for shipping.
     */
    #[Groups(['read'])]
    public ?bool $useForShipping = null;

    /**
     * The default address.
     */
    #[Groups(['read'])]
    public ?bool $defaultAddress = null;

    /**
     * The success.
     */
    #[Groups(['read'])]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[Groups(['read'])]
    public ?string $message = null;
}
