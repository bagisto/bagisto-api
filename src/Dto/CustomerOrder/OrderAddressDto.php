<?php

namespace Webkul\BagistoApi\Dto\CustomerOrder;

use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderAddressDto
{
    /**
     * The id.
     */
    public ?int $id = null;

    /**
     * The address type.
     */
    public ?string $address_type = null;

    /**
     * The first name.
     */
    public ?string $first_name = null;

    /**
     * The last name.
     */
    public ?string $last_name = null;

    /**
     * The gender.
     */
    public ?string $gender = null;

    /**
     * The company name.
     */
    public ?string $company_name = null;

    /**
     * The address.
     */
    public ?string $address = null;

    /**
     * The city.
     */
    public ?string $city = null;

    /**
     * The state.
     */
    public ?string $state = null;

    /**
     * The country.
     */
    public ?string $country = null;

    /**
     * The postcode.
     */
    public ?string $postcode = null;

    /**
     * The email.
     */
    public ?string $email = null;

    /**
     * The phone.
     */
    public ?string $phone = null;

    /**
     * The vat id.
     */
    public ?string $vat_id = null;
}
