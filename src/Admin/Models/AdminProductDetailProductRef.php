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
    protected $table = 'products';

    protected $appends = ['name'];

    protected $casts = ['id' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return DB::table('product_flat')->where('product_id', $this->id)->value('name');
    }
}
