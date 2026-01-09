<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class CustomerAddressOutput
{
    #[Groups(['read'])]
    public ?int $id = null;

    #[Groups(['read'])]
    public ?string $firstName = null;

    #[Groups(['read'])]
    public ?string $lastName = null;

    #[Groups(['read'])]
    public ?string $email = null;

    #[Groups(['read'])]
    public ?string $phone = null;

    #[Groups(['read'])]
    public ?string $company = null;

    #[Groups(['read'])]
    public ?string $address1 = null;

    #[Groups(['read'])]
    public ?string $address2 = null;

    #[Groups(['read'])]
    public ?string $country = null;

    #[Groups(['read'])]
    public ?string $state = null;

    #[Groups(['read'])]
    public ?string $city = null;

    #[Groups(['read'])]
    public ?string $postcode = null;

    #[Groups(['read'])]
    public ?bool $useForShipping = null;

    #[Groups(['read'])]
    public ?bool $defaultAddress = null;

    #[Groups(['read'])]
    public ?bool $success = null;

    #[Groups(['read'])]
    public ?string $message = null;
}
