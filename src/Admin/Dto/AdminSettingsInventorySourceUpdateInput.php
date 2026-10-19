<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsInventorySourceUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/inventory-sources/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The code.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The name.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The contact name.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $contact_name = null;

    /**
     * The contact email.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $contact_email = null;

    /**
     * The contact number.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $contact_number = null;

    /**
     * The contact fax.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $contact_fax = null;

    /**
     * The country.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $country = null;

    /**
     * The state.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $state = null;

    /**
     * The city.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $city = null;

    /**
     * The street.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $street = null;

    /**
     * The postcode.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?string $postcode = null;

    /**
     * The priority.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $priority = null;

    /**
     * The latitude.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $latitude = null;

    /**
     * The longitude.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?float $longitude = null;

    /**
     * The status.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $status = null;
}
