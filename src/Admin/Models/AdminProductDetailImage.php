<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Support\CoreCapabilities;

/**
 * Product image — nested sub-resource of AdminCatalogProduct (`images` connection).
 */
#[ApiResource(
    shortName: 'AdminProductDetailImage',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'type', 'path', 'url', 'position', 'alt_text']],
)]
class AdminProductDetailImage extends Model
{
    /** @var string */
    protected $table = 'product_images';

    /** @var array */
    protected $appends = ['url', 'alt_text'];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'position' => 'int',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::url($this->path) : null;
    }

    /**
     * The alt text stored for the current locale.
     *
     * Read straight off the translation table rather than through the translatable model,
     * which the connection's bare Eloquent parent does not carry.
     */
    #[ApiProperty(writable: false)]
    public function getAltTextAttribute(): ?string
    {
        // BACKWARD COMPATIBILITY: remove when the minimum supported core is 2.4.10.
        if (! app(CoreCapabilities::class)->hasProductImageAltText()) {
            return null;
        }

        return DB::table('product_image_translations')
            ->where('product_image_id', $this->id)
            ->where('locale', app()->getLocale())
            ->value('alt_text');
    }
}
