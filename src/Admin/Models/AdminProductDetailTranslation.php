<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

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
    /**
     * The flat product table that holds each locale and channel copy of the product.
     *
     * @var string
     */
    protected $table = 'product_flat';

    /**
     * The native types the product translation attributes are cast to.
     *
     * @var array
     */
    protected $casts = ['id' => 'int'];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
