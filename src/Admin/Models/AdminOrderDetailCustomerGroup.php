<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Customer group — nested to-one sub-resource of AdminOrderDetailCustomer
 * (the order customer's group). Typed object: customer { group { code name } }.
 */
#[ApiResource(
    shortName: 'AdminOrderDetailCustomerGroup',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'code', 'name']],
)]
class AdminOrderDetailCustomerGroup extends Model
{
    /** @var string */
    protected $table = 'customer_groups';

    /** @var array */
    protected $casts = [
        'id' => 'int',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
