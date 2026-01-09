<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\Product\Models\ProductCustomizableOptionPrice as BaseProductCustomizableOptionPrice;

#[ApiResource(
    operations: [],
    graphQlOperations: [],
)]
class ProductCustomizableOptionPrice extends BaseProductCustomizableOptionPrice
{
    /**
     * Get the customizable option that owns the price.
     */
    public function customizable_option(): BelongsTo
    {
        return $this->belongsTo(ProductCustomizableOption::class, 'product_customizable_option_id');
    }

    /**
     * Get id
     */
    #[ApiProperty(
        identifier: true,
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get label
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Get price
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getPrice(): ?float
    {
        return $this->price ? (float) $this->price : null;
    }

    /**
     * Get sort_order
     */
    #[ApiProperty(
        writable: false,
        readable: true
    )]
    #[Groups(['read'])]
    public function getSort_order(): ?int
    {
        return $this->sort_order;
    }
}
