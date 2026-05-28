<?php

namespace Webkul\BagistoApi\Admin\Dto;

/**
 * Customer block embedded in the order detail.
 *
 * Plain nested DTO — property names are camelCase (the API Platform name
 * converter does not reach nested DTOs, so the property name is the output
 * key verbatim).
 */
#[\ApiPlatform\Metadata\ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class OrderDetailCustomer
{
    #[\ApiPlatform\Metadata\ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $email = null;

    public ?string $name = null;

    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?string $gender = null;

    public ?string $dateOfBirth = null;

    public ?string $phone = null;

    public ?int $status = null;

    public ?OrderDetailCustomerGroup $group = null;
}
