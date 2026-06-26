<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Product video — nested sub-resource of AdminCatalogProduct (`videos` connection).
 */
#[ApiResource(
    shortName: 'AdminProductDetailVideo',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'type', 'path', 'url', 'position']],
)]
class AdminProductDetailVideo extends Model
{
    /** @var string */
    protected $table = 'product_videos';

    /** @var array */
    protected $appends = ['url'];

    /** @var array */
    protected $casts = ['id' => 'int', 'position' => 'int'];

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
}
