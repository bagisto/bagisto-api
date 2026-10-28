<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminProductDetailDownloadableSampleTranslation',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'locale', 'title']],
)]
class AdminProductDetailDownloadableSampleTranslation extends Model
{
    /**
     * The table that stores the downloadable sample translations.
     *
     * @var string
     */
    protected $table = 'product_downloadable_sample_translations';

    /**
     * The native types the downloadable sample translation attributes are cast to.
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
