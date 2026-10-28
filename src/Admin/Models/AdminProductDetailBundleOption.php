<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[ApiResource(
    shortName: 'AdminProductDetailBundleOption',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'label', 'type', 'position', 'is_required', 'products']],
)]
class AdminProductDetailBundleOption extends Model
{
    /**
     * The table that stores the bundle options.
     *
     * @var string
     */
    protected $table = 'product_bundle_options';

    /**
     * The computed attributes appended when the bundle option is serialized.
     *
     * @var array
     */
    protected $appends = ['label', 'position'];

    /**
     * The native types the bundle option attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'is_required' => 'boolean',
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
     * Get label attribute.
     */
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

    /**
     * Get position attribute.
     */
    #[ApiProperty(writable: false)]
    public function getPositionAttribute(): int
    {
        return (int) ($this->sort_order ?? 0);
    }

    /**
     * Products.
     */
    #[ApiProperty(writable: false)]
    public function products(): HasMany
    {
        return $this->hasMany(AdminProductDetailBundleOptionProduct::class, 'product_bundle_option_id')
            ->orderBy('sort_order');
    }
}
