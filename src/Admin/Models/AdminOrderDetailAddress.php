<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminOrderDetailAddress',
    operations: [],
    graphQlOperations: [],

    normalizationContext: ['attributes' => [
        'id', 'address_type', 'first_name', 'last_name', 'company_name', 'vat_id',
        'address', 'city', 'state', 'country', 'postcode', 'email', 'phone',
    ]],
)]
class AdminOrderDetailAddress extends Model
{
    /**
     * The table that stores the addresses.
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * The native types the order address attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
