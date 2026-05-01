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
                summary: 'Get a downloadable link translation by ID',
                description: 'Returns a single locale-specific translation row (`title`) for a downloadable link. Referenced from `/api/shop/products/{id}/downloadable-links` responses via the `translations` IRI list.',
            ),
        ),
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'List downloadable link translations',
                description: 'Lists all downloadable link translation rows. Use the parent product\'s `downloadable-links` sub-resource to scope to one product.',
            ),
        ),
    ],
    graphQlOperations: []
)]
class ProductDownloadableLinkTranslation extends Model
{
    protected $table = 'product_downloadable_link_translations';

    public $timestamps = false;

    protected $fillable = ['title', 'product_downloadable_link_id', 'locale'];

    public function downloadableLink(): BelongsTo
    {
        return $this->belongsTo(ProductDownloadableLink::class, 'product_downloadable_link_id');
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
