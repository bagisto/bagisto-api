<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

#[ApiResource(
    shortName: 'AdminOrderItemPreview',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'sku', 'name', 'qty_ordered', 'product_image']],
)]
class AdminOrderItemPreview extends Model
{
    /** @var string */
    protected $table = 'order_items';

    /** @var array */
    protected $appends = ['product_image'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'qty_ordered' => 'int',
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
     * Get product image attribute.
     */
    #[ApiProperty(writable: false)]
    public function getProductImageAttribute(): ?string
    {
        if (array_key_exists('product_image', $this->attributes)) {
            return $this->attributes['product_image'];
        }

        $productId = $this->attributes['product_id'] ?? null;
        if (! $productId) {
            return null;
        }

        $path = DB::table('product_images')->where('product_id', $productId)->orderBy('id')->value('path');

        return $path ? Storage::url($path) : null;
    }
}
