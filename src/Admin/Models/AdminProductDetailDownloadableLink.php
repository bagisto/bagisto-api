<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[ApiResource(
    shortName: 'AdminProductDetailDownloadableLink',
    operations: [],
    graphQlOperations: [],

    normalizationContext: ['attributes' => [
        'id', 'sort_order', 'downloads', 'price', 'formatted_price', 'type', 'file',
        'file_url', 'sample_file', 'sample_file_url', 'sample_type', 'translations',
    ]],
)]
class AdminProductDetailDownloadableLink extends Model
{
    /** @var string */
    protected $table = 'product_downloadable_links';

    /** @var array */
    protected $appends = ['formatted_price', 'file_url', 'sample_file_url'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'sort_order' => 'int',
        'downloads' => 'int',
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
     * Get price.
     */
    #[ApiProperty(writable: false)]
    public function getPrice(): ?string
    {
        return $this->attributes['price'] !== null ? (string) $this->attributes['price'] : null;
    }

    /**
     * Get formatted price attribute.
     */
    #[ApiProperty(writable: false)]
    public function getFormattedPriceAttribute(): ?string
    {
        return isset($this->attributes['price']) && $this->attributes['price'] !== null
            ? core()->formatBasePrice((float) $this->attributes['price'])
            : null;
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
     * Get sample file url attribute.
     */
    #[ApiProperty(writable: false)]
    public function getSampleFileUrlAttribute(): ?string
    {
        return $this->sample_file ? Storage::url($this->sample_file) : null;
    }

    /**
     * Translations.
     */
    #[ApiProperty(writable: false)]
    public function translations(): HasMany
    {
        return $this->hasMany(AdminProductDetailDownloadableLinkTranslation::class, 'product_downloadable_link_id');
    }
}
