<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ApiResource(
    operations: [],
    graphQlOperations: []
) ]
class ProductDownloadableSampleTranslation extends Model
{
    protected $table = 'product_downloadable_sample_translations';

    public $timestamps = false;

    protected $fillable = ['title', 'product_downloadable_sample_id', 'locale'];

    public function downloadableSample(): BelongsTo
    {
        return $this->belongsTo(ProductDownloadableSample::class, 'product_downloadable_sample_id');
    }

    #[ApiProperty(writable: true, readable: true)]
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $value): void
    {
        $this->title = $value;
    }
}
