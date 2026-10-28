<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[ApiResource(
    shortName: 'AdminProductDetailVideo',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'type', 'path', 'url', 'position']],
)]
class AdminProductDetailVideo extends Model
{
    /**
     * The table that stores the product videos.
     *
     * @var string
     */
    protected $table = 'product_videos';

    /**
     * The computed attributes appended when the product video is serialized.
     *
     * @var array
     */
    protected $appends = ['url'];

    /**
     * The native types the product video attributes are cast to.
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
}
