<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminProductDetailDownloadableLinkTranslation',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'locale', 'title']],
)]
class AdminProductDetailDownloadableLinkTranslation extends Model
{
    /** @var string */
    protected $table = 'product_downloadable_link_translations';

    /** @var array */
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
