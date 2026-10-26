<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminCustomerReviewProductRef',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'name', 'sku']],
)]
class AdminCustomerReviewProductRef extends Model
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
     * Get sku.
     */
    #[ApiProperty(writable: false)]
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * The product name in the admin's locale, else in any locale.
     */
    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        $flat = DB::table('product_flat')->where('product_id', $this->id);

        return (clone $flat)->where('locale', app()->getLocale())->value('name')
            ?? $flat->value('name');
    }
}
