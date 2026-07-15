<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Cart billing/shipping address embedded in the admin draft cart payload.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminCartAddressDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $addressType = null;

    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?string $companyName = null;

    public ?string $email = null;

    /** Stored as a newline-joined string in Bagisto core; exposed as the raw value. */
    public ?string $address = null;

    public ?string $city = null;

    public ?string $state = null;

    public ?string $country = null;

    public ?string $postcode = null;

    public ?string $phone = null;
}
