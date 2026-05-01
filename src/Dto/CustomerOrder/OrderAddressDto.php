<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderAddressDto
{
    public ?int $id = null;

    public ?string $address_type = null;

    public ?string $first_name = null;

    public ?string $last_name = null;

    public ?string $gender = null;

    public ?string $company_name = null;

    public ?string $address = null;

    public ?string $city = null;

    public ?string $state = null;

    public ?string $country = null;

    public ?string $postcode = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $vat_id = null;
}
