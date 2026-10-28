<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCartAddressDto
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The address type.
     */
    public ?string $addressType = null;

    /**
     * The first name.
     */
    public ?string $firstName = null;

    /**
     * The last name.
     */
    public ?string $lastName = null;

    /**
     * The company name.
     */
    public ?string $companyName = null;

    /**
     * The email.
     */
    public ?string $email = null;

    /**
     * Stored as a newline-joined string in Bagisto core; exposed as the raw value.
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
     * The phone.
     */
    public ?string $phone = null;
}
