<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ApiResource(
    shortName: 'AdminOrderDetailCustomer',
    operations: [],
    graphQlOperations: [],

    normalizationContext: ['attributes' => [
        'id', 'email', 'name', 'first_name', 'last_name', 'gender', 'date_of_birth',
        'phone', 'status', 'group',
    ]],
)]
class AdminOrderDetailCustomer extends Model
{
    /**
     * The table that stores the customers.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The computed attributes appended when the order customer is serialized.
     *
     * @var array
     */
    protected $appends = ['name'];

    /**
     * The native types the order customer attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'status' => 'int',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get name attribute.
     */
    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: null;
    }

    /**
     * Group.
     */
    #[ApiProperty(writable: false)]
    public function group(): BelongsTo
    {
        return $this->belongsTo(AdminOrderDetailCustomerGroup::class, 'customer_group_id');
    }
}
