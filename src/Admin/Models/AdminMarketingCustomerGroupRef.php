<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminMarketingCustomerGroupRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name']],
)]
class AdminMarketingCustomerGroupRef extends Model
{
    /**
     * The table that stores the customer groups.
     *
     * @var string
     */
    protected $table = 'customer_groups';

    /**
     * Indicates the customer group reference does not maintain created and updated timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The native types the customer group reference attributes are cast to.
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
        return $this->id !== null ? (int) $this->id : null;
    }
}
