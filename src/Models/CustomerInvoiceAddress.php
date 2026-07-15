<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Customer Invoice Address — nested API resource (no standalone endpoints).
 *
 * Exposed only as a relationship of CustomerInvoice.
 * Points to the `addresses` table filtered by address_type (invoice_billing/invoice_shipping).
 */
#[ApiResource(
    shortName: 'CustomerInvoiceAddress',
    operations: [],
    graphQlOperations: [],
)]
class CustomerInvoiceAddress extends Model
{
    /** @var string */
    protected $table = 'addresses';

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'country_id' => 'string',
        'customer_id' => 'int',
        'cart_id' => 'int',
        'order_id' => 'int',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'postcode' => 'string',
        'phone' => 'string',
        'address_type' => 'string',
        'country' => 'string',
        'company_name' => 'string',
        'gender' => 'string',
        'vat_id' => 'string',
        'default_address' => 'boolean',
        'additional' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array */
    protected $appends = ['name'];

    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Full name computed from first + last name.
     * Using Laravel's accessor pattern: getNameAttribute() -> 'name' property
     */
    #[ApiProperty(writable: false, description: 'Full name')]
    public function getNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Explicit getter for address
     */
    #[ApiProperty(writable: false)]
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * Explicit getter for city
     */
    #[ApiProperty(writable: false)]
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Explicit getter for state
     */
    #[ApiProperty(writable: false)]
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Explicit getter for postcode
     */
    #[ApiProperty(writable: false)]
    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * Explicit getter for country ID
     */
    #[ApiProperty(writable: false)]
    public function getCountryId(): ?string
    {
        return $this->country_id;
    }

    /**
     * Explicit getter for phone
     */
    #[ApiProperty(writable: false)]
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Explicit getter for address type
     */
    #[ApiProperty(writable: false)]
    public function getAddressType(): ?string
    {
        return $this->address_type;
    }

    /**
     * toArray override to ensure all address fields are included
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        // Ensure all critical address fields are in the array
        $array['id'] = $this->id;
        $array['name'] = $this->name;
        $array['address'] = $this->address;
        $array['city'] = $this->city;
        $array['state'] = $this->state;
        $array['postcode'] = $this->postcode;
        $array['address_type'] = $this->address_type;
        $array['country_id'] = $this->country_id;
        $array['phone'] = $this->phone;

        return $array;
    }
}
