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
    protected $table = 'products';

    protected $appends = ['name', 'price', 'formatted_price', 'quantity', 'in_stock'];

    protected $casts = ['id' => 'int'];

    private ?object $flat = null;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return $this->flatRow()->name ?? null;
    }

    #[ApiProperty(writable: false)]
    public function getPriceAttribute(): ?string
    {
        $p = $this->flatRow()->price ?? null;

        return $p !== null ? (string) $p : null;
    }

    #[ApiProperty(writable: false)]
    public function getFormattedPriceAttribute(): ?string
    {
        $p = $this->flatRow()->price ?? null;

        return $p !== null ? core()->formatBasePrice((float) $p) : null;
    }

    #[ApiProperty(writable: false)]
    public function getQuantityAttribute(): int
    {
        return (int) DB::table('product_inventories')->where('product_id', $this->id)->sum('qty');
    }

    #[ApiProperty(writable: false)]
    public function getInStockAttribute(): bool
    {
        return $this->getQuantityAttribute() > 0;
    }

    #[ApiProperty(writable: false)]
    public function attribute_values(): HasMany
    {
        return $this->hasMany(AdminProductDetailAttributeValue::class, 'product_id');
    }

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
