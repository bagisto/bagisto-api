<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[ApiResource(
    shortName: 'AdminProductDetailDownloadableSample',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'sort_order', 'type', 'file', 'file_url', 'translations']],
)]
class AdminProductDetailDownloadableSample extends Model
{
    /**
     * The table that stores the downloadable samples.
     *
     * @var string
     */
    protected $table = 'product_downloadable_samples';

    /**
     * The computed attributes appended when the downloadable sample is serialized.
     *
     * @var array
     */
    protected $appends = ['file_url'];

    /**
     * The native types the downloadable sample attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
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
     * Get file url attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFileUrlAttribute(): ?string
    {
        return $this->file ? Storage::url($this->file) : null;
    }

    /**
     * Translations.
     */
    #[ApiProperty(writable: false)]
    public function translations(): HasMany
    {
        return $this->hasMany(AdminProductDetailDownloadableSampleTranslation::class, 'product_downloadable_sample_id');
    }
}
