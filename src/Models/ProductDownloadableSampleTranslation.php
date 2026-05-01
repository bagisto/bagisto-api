<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'Get a downloadable sample translation by ID',
                description: 'Returns a single locale-specific translation row (`title`) for a downloadable sample. Referenced from `/api/shop/products/{id}/downloadable-samples` responses via the `translations` IRI list.',
            ),
        ),
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'List downloadable sample translations',
                description: 'Lists all downloadable sample translation rows. Use the parent product\'s `downloadable-samples` sub-resource to scope to one product.',
            ),
        ),
    ],
    graphQlOperations: []
)]
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
