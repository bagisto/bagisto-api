<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminProductDetailVariant',
    operations: [],
    graphQlOperations: [],

    normalizationContext: ['attributes' => [
        'id', 'sku', 'name', 'price', 'formatted_price', 'quantity', 'in_stock', 'attribute_values',
    ]],
)]
class AdminProductDetailVariant extends Model
{
    /**
     * The table.
     */
    protected $table = 'products';

    /**
     * The appends.
     */
    protected $appends = ['name', 'price', 'formatted_price', 'quantity', 'in_stock'];

    /**
     * The casts.
     */
    protected $casts = ['id' => 'int'];

    /**
     * The flat.
     */
    private ?object $flat = null;

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
        return $this->flatRow()->name ?? null;
    }

    /**
     * Get price attribute.
     */
    #[ApiProperty(writable: false)]
    public function getPriceAttribute(): ?string
    {
        $price = $this->flatRow()->price ?? null;

        return $price !== null ? (string) $price : null;
    }

    /**
     * Get formatted price attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedPriceAttribute(): ?string
    {
        $price = $this->flatRow()->price ?? null;

        return $price !== null ? core()->formatBasePrice((float) $price) : null;
    }

    /**
     * Get quantity attribute.
     */
    #[ApiProperty(writable: false)]
    public function getQuantityAttribute(): int
    {
        return (int) DB::table('product_inventories')->where('product_id', $this->id)->sum('qty');
    }

    /**
     * Get in stock attribute.
     */
    #[ApiProperty(writable: false)]
    public function getInStockAttribute(): bool
    {
        return $this->getQuantityAttribute() > 0;
    }

    /**
     * Attribute values.
     */
    #[ApiProperty(writable: false)]
    public function attribute_values(): HasMany
    {
        return $this->hasMany(AdminProductDetailAttributeValue::class, 'product_id');
    }

    /**
     * Flat row.
     */
    private function flatRow(): object
    {
        if ($this->flat === null) {
            $this->flat = DB::table('product_flat')->where('product_id', $this->id)
                ->orderByRaw('locale = ? desc', [app()->getLocale()])
                ->first() ?? (object) [];
        }

        return $this->flat;
    }
}
