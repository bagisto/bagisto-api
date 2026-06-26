<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Slim refund summary — nested sub-resource of AdminOrderDetail. For the full
 * refund use GET /api/admin/refunds/{id}.
 */
#[ApiResource(
    shortName: 'AdminOrderDetailRefund',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => [
        'id', 'state', 'total_qty', 'grand_total', 'base_grand_total', 'created_at',
    ]],
)]
class AdminOrderDetailRefund extends Model
{
    /** @var string */
    protected $table = 'refunds';

    /** @var array */
    protected $casts = [
        'id'               => 'int',
        'total_qty'        => 'int',
        'grand_total'      => 'float',
        'base_grand_total' => 'float',
        'created_at'       => 'datetime',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
