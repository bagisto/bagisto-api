<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminProductDetailBundleOptionProduct',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'product_id', 'sku', 'name', 'qty', 'is_default', 'sort_order']],
)]
class AdminProductDetailBundleOptionProduct extends Model
{
    /**
     * The table that stores the bundle option products.
     *
     * @var string
     */
    protected $table = 'product_bundle_option_products';

    /**
     * The computed attributes appended when the bundle option product is serialized.
     *
     * @var array
     */
    protected $appends = ['sku', 'name'];

    /**
     * The native types the bundle option product attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'product_id' => 'int',
        'qty' => 'int',
        'is_default' => 'boolean',
        'sort_order' => 'int',
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
     * Get sku attribute.
     */
    #[ApiProperty(writable: false)]
    public function getSkuAttribute(): ?string
    {
        return DB::table('products')->where('id', $this->product_id)->value('sku');
    }

    /**
     * Get name attribute.
     */
    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return DB::table('product_flat')->where('product_id', $this->product_id)->value('name');
    }
}
