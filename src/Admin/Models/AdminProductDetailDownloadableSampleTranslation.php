<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/** Downloadable-sample translation — nested in the downloadableSamples connection. */
#[ApiResource(
    shortName: 'AdminProductDetailDownloadableSampleTranslation',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'locale', 'title']],
)]
class AdminProductDetailDownloadableSampleTranslation extends Model
{
    /** @var string */
    protected $table = 'product_downloadable_sample_translations';

    /** @var array */
    protected $casts = ['id' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
