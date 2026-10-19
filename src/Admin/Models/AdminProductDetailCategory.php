<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        return $this->translationRow()->name ?? null;
    }

    /**
     * Get slug attribute.
     */
    #[ApiProperty(writable: false)]
    public function getSlugAttribute(): ?string
    {
        return $this->translationRow()->slug ?? null;
    }

    /**
     * Translation row.
     */
    private function translationRow(): object
    {
        return DB::table('category_translations')->where('category_id', $this->id)
            ->orderByRaw('locale = ? desc', [app()->getLocale()])
            ->first() ?? (object) [];
    }
}
