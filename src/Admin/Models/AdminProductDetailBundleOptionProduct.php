<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Bundle-option product — nested in the `bundleOptions` connection
 * (`bundleOptions { edges { node { products { edges { node } } } } }`).
 */
#[ApiResource(
    shortName: 'AdminProductDetailBundleOptionProduct',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'product_id', 'sku', 'name', 'qty', 'is_default', 'sort_order']],
)]
class AdminProductDetailBundleOptionProduct extends Model
{
    /** @var string */
    protected $table = 'product_bundle_option_products';

    /** @var array */
    protected $appends = ['sku', 'name'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'product_id' => 'int',
        'qty' => 'int',
        'is_default' => 'boolean',
        'sort_order' => 'int',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getSkuAttribute(): ?string
    {
        return DB::table('products')->where('id', $this->product_id)->value('sku');
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return DB::table('product_flat')->where('product_id', $this->product_id)->value('name');
    }
}
