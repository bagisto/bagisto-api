<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Customer Order Payment — nested API resource (no standalone endpoints).
 *
 * Exposed only as a relationship of CustomerOrder.
 * Points to the `order_payment` table.
 */
#[ApiResource(
    shortName: 'CustomerOrderPayment',
    operations: [],
    graphQlOperations: [],
)]
class CustomerOrderPayment extends Model
{
    /** @var string */
    protected $table = 'order_payment';

    /** @var array */
    protected $casts = [
        'id'           => 'int',
        'order_id'     => 'int',
        'method'       => 'string',
        'method_title' => 'string',
        'additional'   => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }
}
