<?php

namespace Webkul\BagistoApi\Admin\Dto;

/**
 * Billing / shipping address block embedded in the order detail.
 */
#[\ApiPlatform\Metadata\ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailAddress
{
    #[\ApiPlatform\Metadata\ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $addressType = null;

    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?string $companyName = null;

    public ?string $vatId = null;

    public ?string $address = null;

    public ?string $city = null;

    public ?string $state = null;

    public ?string $country = null;

    public ?string $postcode = null;

    public ?string $email = null;

    public ?string $phone = null;
}
