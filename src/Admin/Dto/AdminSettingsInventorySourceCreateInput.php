<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsInventorySourceCreateInput
{
    /**
     * The code.
     */
    #[ApiProperty(description: 'Unique source code (alpha-dash).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The name.
     */
    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(description: 'Free-text description.')]
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
    #[ApiProperty(description: 'Sorting priority (integer, default 0).')]
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
    #[ApiProperty(description: '0 = inactive, 1 = active.')]
    #[Groups(['mutation'])]
    public ?int $status = null;
}
