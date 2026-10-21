<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminProductDetailCustomizableOptionTranslation',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'locale', 'label']],
)]
class AdminProductDetailCustomizableOptionTranslation extends Model
{
    /**
     * The table that stores the customizable option translations.
     *
     * @var string
     */
    protected $table = 'product_customizable_option_translations';

    /**
     * The native types the customizable option translation attributes are cast to.
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
