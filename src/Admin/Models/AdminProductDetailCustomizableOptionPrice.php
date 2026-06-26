<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

/** Customizable-option price — nested in the customizableOptions connection. */
#[ApiResource(
    shortName: 'AdminProductDetailCustomizableOptionPrice',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'label', 'price', 'sort_order']],
)]
class AdminProductDetailCustomizableOptionPrice extends Model
{
    /** @var string */
    protected $table = 'product_customizable_option_prices';

    /** @var array */
    protected $casts = ['id' => 'int', 'sort_order' => 'int'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function getPrice(): ?string
    {
        return $this->attributes['price'] !== null ? (string) $this->attributes['price'] : null;
    }
}
