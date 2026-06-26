<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Product category — nested sub-resource of AdminCatalogProduct (`categories`
 * connection). name/slug resolved from category_translations (current locale).
 */
#[ApiResource(
    shortName: 'AdminProductDetailCategory',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'name', 'slug']],
)]
class AdminProductDetailCategory extends Model
{
    /** @var string */
    protected $table = 'categories';

    /** @var array */
    protected $appends = ['name', 'slug'];

    /** @var array */
    protected $casts = ['id' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return $this->translationRow()->name ?? null;
    }

    #[ApiProperty(writable: false)]
    public function getSlugAttribute(): ?string
    {
        return $this->translationRow()->slug ?? null;
    }

    private function translationRow(): object
    {
        return DB::table('category_translations')->where('category_id', $this->id)
            ->orderByRaw('locale = ? desc', [app()->getLocale()])
            ->first() ?? (object) [];
    }
}
