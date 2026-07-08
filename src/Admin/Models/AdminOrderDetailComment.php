<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Order comment — nested sub-resource of AdminOrderDetail (the order-view
 * Comments panel, newest first).
 */
#[ApiResource(
    shortName: 'AdminOrderDetailComment',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'comment', 'customer_notified', 'created_at']],
)]
class AdminOrderDetailComment extends Model
{
    /** @var string */
    protected $table = 'order_comments';

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'customer_notified' => 'boolean',
        'created_at' => 'datetime',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
