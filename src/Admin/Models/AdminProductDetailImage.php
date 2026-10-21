<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Support\CoreCapabilities;

#[ApiResource(
    shortName: 'AdminProductDetailImage',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'type', 'path', 'url', 'position', 'alt_text']],
)]
class AdminProductDetailImage extends Model
{
    /**
     * The table that stores the product images.
     *
     * @var string
     */
    protected $table = 'product_images';

    /**
     * The computed attributes appended when the product image is serialized.
     *
     * @var array
     */
    protected $appends = ['url', 'alt_text'];

    /**
     * The native types the product image attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'position' => 'int',
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
     * Get url attribute.
     */
    #[ApiProperty(writable: false)]
    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::url($this->path) : null;
    }

    /**
     * The alt text stored for the current locale.
     * Read straight off the translation table rather than through the translatable model,.
     */
    #[ApiProperty(writable: false)]
    public function getAltTextAttribute(): ?string
    {
        if (! app(CoreCapabilities::class)->hasProductImageAltText()) {
            return null;
        }

        return DB::table('product_image_translations')
            ->where('product_image_id', $this->id)
            ->where('locale', app()->getLocale())
            ->value('alt_text');
    }
}
