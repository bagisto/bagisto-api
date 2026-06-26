<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Product per-locale translation row (product_flat) — nested sub-resource of
 * AdminCatalogProduct (`translations` connection).
 */
#[ApiResource(
    shortName: 'AdminProductDetailTranslation',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => [
        'id', 'locale', 'name', 'description', 'short_description', 'url_key',
        'meta_title', 'meta_description', 'meta_keywords',
    ]],
)]
class AdminProductDetailTranslation extends Model
{
    /** @var string */
    protected $table = 'product_flat';

    /** @var array */
    protected $casts = ['id' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
