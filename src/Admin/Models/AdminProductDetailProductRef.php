<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminProductDetailProductRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'sku', 'type', 'name']],
)]
class AdminProductDetailProductRef extends Model
{
    /**
     * The table.
     */
    protected $table = 'products';

    /**
     * The appends.
     */
    protected $appends = ['name'];

    /**
     * The casts.
     */
    protected $casts = ['id' => 'int'];

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
        return DB::table('product_flat')->where('product_id', $this->id)->value('name');
    }
}
