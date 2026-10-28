<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

#[ApiResource(
    shortName: 'AdminProductDetailCustomizableOptionPrice',
    operations: [],
    graphQlOperations: [],
    normalizationContext: ['attributes' => ['id', 'label', 'price', 'sort_order']],
)]
class AdminProductDetailCustomizableOptionPrice extends Model
{
    /**
     * The table that stores the customizable option prices.
     *
     * @var string
     */
    protected $table = 'product_customizable_option_prices';

    /**
     * The native types the customizable option price attributes are cast to.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'sort_order' => 'int',
    ];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get price.
     */
    #[ApiProperty(writable: false)]
    public function getPrice(): ?string
    {
        return $this->attributes['price'] !== null ? (string) $this->attributes['price'] : null;
    }
}
