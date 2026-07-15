<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Order line-item — nested sub-resource of AdminOrderDetail.
 *
 * Eloquent #[ApiResource] (operations: []) so the parent's `items` HasMany
 * resolves as a GraphQL connection (`items { edges { node } }`) with real node
 * data. Money is exposed raw (value + base value); clients format. `children`
 * is a self-referencing connection (configurable/bundle/grouped child lines);
 * `child` / `downloadableLinks` / `additional` are bare JSON leaves.
 */
#[ApiResource(
    shortName: 'AdminOrderDetailItem',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => [
        'id', 'sku', 'type', 'name', 'product_id', 'weight',
        'qty_ordered', 'qty_shipped', 'qty_invoiced', 'qty_canceled', 'qty_refunded',
        'price', 'base_price', 'total', 'base_total', 'tax_amount', 'tax_percent',
        'discount_amount', 'discount_percent', 'additional', 'child', 'children', 'downloadable_links', 'created_at',
    ]],
)]
class AdminOrderDetailItem extends Model
{
    /** @var string */
    protected $table = 'order_items';

    /** @var array */
    protected $appends = ['child', 'downloadable_links'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'product_id' => 'int',
        'weight' => 'float',
        'qty_ordered' => 'int',
        'qty_shipped' => 'int',
        'qty_invoiced' => 'int',
        'qty_canceled' => 'int',
        'qty_refunded' => 'int',
        'price' => 'float',
        'base_price' => 'float',
        'total' => 'float',
        'base_total' => 'float',
        'tax_amount' => 'float',
        'tax_percent' => 'float',
        'discount_amount' => 'float',
        'discount_percent' => 'float',
        'additional' => 'array',
        'created_at' => 'datetime',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Child lines (configurable/bundle/grouped) → nested connection.
     */
    #[ApiProperty(writable: false)]
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The single configurable child (mirrors the order-view item.child). Bare
     * JSON leaf — the simple variant row bought under a configurable parent.
     */
    #[ApiProperty(writable: false)]
    public function getChildAttribute(): ?array
    {
        $child = static::where('parent_id', $this->id)->first();

        return $child ? $child->only(['id', 'sku', 'type', 'name', 'qty_ordered', 'price']) : null;
    }

    /**
     * Purchased downloadable links (downloadable products). Bare JSON leaf.
     */
    #[ApiProperty(writable: false)]
    public function getDownloadableLinksAttribute(): array
    {
        $rows = DB::table('downloadable_link_purchased')
            ->where('order_item_id', $this->id)
            ->get();

        return $rows->map(fn ($r) => (array) $r)->all();
    }
}
