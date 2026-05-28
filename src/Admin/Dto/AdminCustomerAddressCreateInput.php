<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCustomerAddressCreateInput
{
    #[ApiProperty] #[Groups(['mutation'])]
    public ?int $customer_id = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $first_name = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $last_name = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $company_name = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $vat_id = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $address = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $city = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $state = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $country = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $postcode = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $email = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?string $phone = null;

    #[ApiProperty] #[Groups(['mutation'])]
    public ?bool $default_address = null;
}
