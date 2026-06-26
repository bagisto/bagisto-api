<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/** Customizable-option translation — nested in the customizableOptions connection. */
#[ApiResource(
    shortName: 'AdminProductDetailCustomizableOptionTranslation',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'locale', 'label']],
)]
class AdminProductDetailCustomizableOptionTranslation extends Model
{
    /** @var string */
    protected $table = 'product_customizable_option_translations';

    /** @var array */
    protected $casts = ['id' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
