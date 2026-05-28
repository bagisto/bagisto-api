<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/inventory-sources/{id} and the GraphQL
 * update/delete mutations (delete reuses the same input — only `id` is required).
 */
class AdminSettingsInventorySourceUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/inventory-sources/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $description = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $contact_name = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $contact_email = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $contact_number = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $contact_fax = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $country = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $state = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $city = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $street = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $postcode = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $priority = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $latitude = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $longitude = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;
}
