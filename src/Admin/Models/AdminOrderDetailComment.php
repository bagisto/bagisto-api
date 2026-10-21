<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminOrderDetailComment',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'comment', 'customer_notified', 'created_at']],
)]
class AdminOrderDetailComment extends Model
{
    /**
     * The table that stores the order comments.
     *
     * @var string
     */
    protected $table = 'order_comments';

    /**
     * The native types the order comment attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'customer_notified' => 'boolean',
        'created_at' => 'datetime',
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
