<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Bundle option — nested sub-resource of AdminCatalogProduct (`bundleOptions`
 * connection). `products` is a nested connection; `label` is locale-resolved.
 */
#[ApiResource(
    shortName: 'AdminProductDetailBundleOption',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'label', 'type', 'position', 'is_required', 'products']],
)]
class AdminProductDetailBundleOption extends Model
{
    /** @var string */
    protected $table = 'product_bundle_options';

    /** @var array */
    protected $appends = ['label', 'position'];

    /** @var array */
    protected $casts = ['id' => 'int', 'is_required' => 'boolean'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getLabelAttribute(): ?string
    {
        if (! DB::getSchemaBuilder()->hasTable('product_bundle_option_translations')) {
            return null;
        }

        return DB::table('product_bundle_option_translations')
            ->where('product_bundle_option_id', $this->id)
            ->orderByRaw('locale = ? desc', [app()->getLocale()])
            ->value('label');
    }

    #[ApiProperty(writable: false)]
    public function getPositionAttribute(): int
    {
        return (int) ($this->sort_order ?? 0);
    }

    #[ApiProperty(writable: false)]
    public function products(): HasMany
    {
        return $this->hasMany(AdminProductDetailBundleOptionProduct::class, 'product_bundle_option_id')
            ->orderBy('sort_order');
    }
}
