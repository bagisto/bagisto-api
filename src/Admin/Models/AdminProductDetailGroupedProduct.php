<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminProductDetailGroupedProduct',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'associated_product_id', 'sku', 'name', 'qty', 'sort_order']],
)]
class AdminProductDetailGroupedProduct extends Model
{
    protected $table = 'product_grouped_products';

    protected $appends = ['sku', 'name'];

    protected $casts = [
        'id'                    => 'int',
        'associated_product_id' => 'int',
        'qty'                   => 'int',
        'sort_order'            => 'int',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getSkuAttribute(): ?string
    {
        return DB::table('products')->where('id', $this->associated_product_id)->value('sku');
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return DB::table('product_flat')->where('product_id', $this->associated_product_id)->value('name');
    }
}
