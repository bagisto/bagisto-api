<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'CustomerOrderAddress',
    operations: [],
    graphQlOperations: [],
)]
class CustomerOrderAddress extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * The attributes cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'address_type' => 'string',
        'customer_id' => 'int',
        'order_id' => 'int',
        'first_name' => 'string',
        'last_name' => 'string',
        'gender' => 'string',
        'company_name' => 'string',
        'address' => 'string',
        'city' => 'string',
        'state' => 'string',
        'country' => 'string',
        'postcode' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'vat_id' => 'string',
        'default_address' => 'boolean',
        'additional' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * API Platform identifier.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Full name computed from first + last name.
     */
    #[ApiProperty(writable: false, description: 'Full name')]
    public function getNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
