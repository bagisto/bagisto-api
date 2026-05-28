<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/inventory-sources.
 *
 * Mirrors Bagisto admin InventorySourceController::store validation:
 *   - code: required, unique, alpha-dash (Code rule)
 *   - name, contact_name, contact_email (email), contact_number, country,
 *     state, city, street, postcode: required (when present)
 *   - priority: numeric
 *   - status: boolean / 0|1
 */
class AdminSettingsInventorySourceCreateInput
{
    #[ApiProperty(description: 'Unique source code (alpha-dash).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Free-text description.')]
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

    #[ApiProperty(description: 'Sorting priority (integer, default 0).')]
    #[Groups(['mutation'])]
    public ?int $priority = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $latitude = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $longitude = null;

    #[ApiProperty(description: '0 = inactive, 1 = active.')]
    #[Groups(['mutation'])]
    public ?int $status = null;
}
